<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentEvent;
use App\Models\DocumentRoute;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DocumentWorkflow
{
    public function __construct(private TrackingNumberAllocator $numbers) {}

    public function create(User $actor, array $data): Document
    {
        Gate::forUser($actor)->authorize('create', Document::class);
        // Intentionally outside the atomic document/route/event transaction.
        $number = $this->numbers->reserve();

        return DB::transaction(function () use ($actor, $data, $number) {
            [$sender, $recipient] = $this->participants($actor, (int) $data['recipient_id'], (int) $data['department_id']);
            $document = Document::create($number + ['title' => $data['title'], 'description' => $data['description'] ?? null, 'created_by' => $sender->id]);
            $route = $this->assign($document, $sender, $recipient, $data['remarks'] ?? null);
            $this->event($document, $route, $sender, 'created', new: ['title' => $document->title, 'description' => $document->description]);
            $this->event($document, $route, $sender, 'sent', remarks: $data['remarks'] ?? null);

            return $document;
        }, 3);
    }

    public function transition(User $actor, Document $document, string $action, array $data): Document
    {
        abort_unless(in_array($action, ['receive', 'decline', 'forward', 'close'], true), 404);

        return DB::transaction(function () use ($actor, $document, $action, $data) {
            $document = Document::whereKey($document->id)->lockForUpdate()->firstOrFail();
            $route = DocumentRoute::whereKey($document->current_route_id)->lockForUpdate()->firstOrFail();
            $document->setRelation('currentRoute', $route);
            if ((int) $data['route_id'] !== $route->id) {
                throw ValidationException::withMessages(['route_id' => 'This assignment has changed. Reload the document before acting.']);
            }
            // Check ownership before resolving return/forward recipients.
            Gate::forUser($actor)->authorize($action, $document);
            if ($action === 'forward') {
                [$actor, $recipient] = $this->participants($actor, (int) $data['recipient_id'], (int) $data['department_id']);
            } elseif ($action === 'decline') {
                $previous = User::find($route->from_user_id);
                if (! $previous || ! $previous->department_id) {
                    throw ValidationException::withMessages(['reason' => 'The previous sender is unavailable. The document has not been changed.']);
                }
                [$actor, $recipient] = $this->participants($actor, $previous->id, $previous->department_id, returning: true);
            } else {
                $actor = User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            }
            Gate::forUser($actor)->authorize($action, $document);
            $remarks = $data['remarks'] ?? null;
            if ($action === 'receive') {
                $route->update(['status' => 'received', 'received_at' => now()]);
                $this->event($document, $route, $actor, 'received');
            } elseif ($action === 'decline') {
                if (trim($data['reason'] ?? '') === '') {
                    throw ValidationException::withMessages(['reason' => 'A decline reason is required.']);
                }
                $route->update(['status' => 'declined', 'declined_at' => now()]);
                $this->event($document, $route, $actor, 'declined', remarks: $data['reason']);
                $returned = $this->assign($document, $actor, $recipient, $data['reason']);
                $this->event($document, $returned, $actor, 'returned', remarks: $data['reason']);
            } elseif ($action === 'forward') {
                if (isset($data['title']) && $data['title'] !== $document->title) {
                    $oldTitle = $document->title;
                    $document->update(['title' => $data['title']]);
                    $this->event($document, $route, $actor, 'title_changed', ['title' => $oldTitle], ['title' => $data['title']]);
                }
                $route->update(['status' => 'forwarded']);
                $next = $this->assign($document, $actor, $recipient, $remarks);
                $this->event($document, $next, $actor, 'forwarded', ['route_id' => $route->id], ['route_id' => $next->id], $remarks);
            } else {
                $route->update(['status' => 'closed', 'closed_at' => now()]);
                $this->event($document, $route, $actor, 'closed', remarks: $remarks);
            }
            $document->touch();

            return $document->unsetRelation('currentRoute');
        }, 3);
    }

    public function view(User $actor, Document $document): Document
    {
        return DB::transaction(function () use ($actor, $document) {
            $document = Document::whereKey($document->id)->lockForUpdate()->firstOrFail();
            $route = DocumentRoute::whereKey($document->current_route_id)->lockForUpdate()->firstOrFail();
            $document->setRelation('currentRoute', $route);
            $actor = User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('view', $document);
            if ($route->to_user_id === $actor->id && in_array($route->status, ['pending', 'received'], true) && ! $route->seen_at) {
                $route->update(['seen_at' => now()]);
                $this->event($document, $route, $actor, 'seen');
                $document->touch();
            }

            return $document;
        }, 3);
    }

    private function participants(User $actor, int $recipientId, int $departmentId, bool $returning = false): array
    {
        // Department -> user lock order matches account management. Revalidate after locking.
        $departments = Department::orderBy('id')->lockForUpdate()->get();
        $users = User::whereIn('id', [$actor->id, $recipientId])->orderBy('id')->lockForUpdate()->get();
        $actor = $users->firstWhere('id', $actor->id);
        $recipient = $users->firstWhere('id', $recipientId);
        abort_unless($actor?->is_active, 403);
        if (! $recipient?->is_active || $recipient->department_id !== $departmentId || ! $departments->firstWhere('id', $departmentId)?->is_active) {
            throw ValidationException::withMessages([$returning ? 'reason' : 'recipient_id' => $returning
                ? 'The previous sender or their department is inactive or unavailable. The document has not been changed.'
                : 'Choose an active recipient in the selected active department.']);
        }
        // Returning must remain possible even if the declining recipient's department was deactivated.
        if (! $actor->department_id || (! $returning && ! $departments->firstWhere('id', $actor->department_id)?->is_active)) {
            throw ValidationException::withMessages(['department_id' => 'Your account must belong to an active department to send documents.']);
        }

        return [$actor, $recipient];
    }

    private function assign(Document $document, User $sender, User $recipient, ?string $remarks): DocumentRoute
    {
        $route = $document->routes()->create([
            'from_user_id' => $sender->id, 'from_department_id' => $sender->department_id,
            'to_user_id' => $recipient->id, 'to_department_id' => $recipient->department_id,
            'status' => 'pending', 'sent_at' => now(), 'remarks' => $remarks,
        ]);
        $document->update(['current_route_id' => $route->id]);

        return $route;
    }

    private function event(Document $document, DocumentRoute $route, User $actor, string $type, ?array $previous = null, ?array $new = null, ?string $remarks = null): void
    {
        $actor->loadMissing('department');
        $route->loadMissing(['sender', 'recipient', 'fromDepartment', 'toDepartment']);
        DocumentEvent::create([
            'document_id' => $document->id, 'document_route_id' => $route->id, 'actor_id' => $actor->id,
            'event_type' => $type, 'previous_value' => $previous, 'new_value' => $new, 'remarks' => $remarks, 'created_at' => now(),
            'context' => [
                'actor' => $actor->name, 'actor_department' => $actor->department?->name,
                'sender' => $route->sender->name, 'from_department' => $route->fromDepartment->name,
                'recipient' => $route->recipient->name, 'to_department' => $route->toDepartment->name,
            ],
        ]);
    }
}
