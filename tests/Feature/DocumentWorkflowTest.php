<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentEvent;
use App\Models\DocumentRoute;
use App\Models\User;
use App\Services\DocumentWorkflow;
use App\Services\TrackingNumberAllocator;
use App\Support\DocumentOverview;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Department $origin;

    private Department $destination;

    private User $sender;

    private User $recipient;

    private User $next;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->origin = Department::create(['name' => 'Origin', 'code' => 'ORG', 'is_active' => true]);
        $this->destination = Department::create(['name' => 'Destination', 'code' => 'DST', 'is_active' => true]);
        $this->sender = User::factory()->create(['department_id' => $this->origin->id]);
        $this->recipient = User::factory()->create(['department_id' => $this->destination->id]);
        $this->next = User::factory()->create(['department_id' => $this->origin->id]);
        $this->admin = User::factory()->create(['department_id' => $this->origin->id, 'role' => 'admin']);
    }

    private function payload(array $extra = []): array
    {
        return array_replace(['title' => 'Office memorandum', 'description' => 'Please review.', 'department_id' => $this->destination->id, 'recipient_id' => $this->recipient->id], $extra);
    }

    private function send(): Document
    {
        return app(DocumentWorkflow::class)->create($this->sender, $this->payload());
    }

    private function act(User $actor, Document $document, string $action, array $extra = [])
    {
        return $this->actingAs($actor)->put(route('documents.'.$action, $document), array_replace(['route_id' => $document->fresh()->current_route_id], $extra));
    }

    public function test_document_creation_records_initial_assignment_and_events_atomically(): void
    {
        $this->actingAs($this->sender)->post(route('documents.store'), $this->payload())->assertSessionHasNoErrors()->assertRedirect();
        $document = Document::firstOrFail();
        $this->assertMatchesRegularExpression('/^DR-'.now('Asia/Manila')->year.'-\d{6,}$/', $document->tracking_number);
        $this->assertSame($this->sender->id, $document->created_by);
        $this->assertSame('pending', $document->currentRoute->status);
        $this->assertSame($this->recipient->id, $document->currentRoute->to_user_id);
        $this->assertSame($this->destination->id, $document->currentRoute->to_department_id);
        $this->assertNull($document->currentRoute->seen_at);
        $this->assertSame(['created', 'sent'], $document->events->pluck('event_type')->all());
        $this->assertDatabaseCount('document_routes', 1);
    }

    public function test_tracking_reservations_are_unique_and_use_database_identity_not_row_counts(): void
    {
        $first = $this->send();
        $second = $this->send();
        $this->assertNotSame($first->tracking_number, $second->tracking_number);
        $reservation = app(TrackingNumberAllocator::class)->reserve();
        $third = $this->send();
        $this->assertGreaterThan($reservation['document_number_id'], $third->document_number_id);
        $this->expectException(QueryException::class);
        DB::table('documents')->where('id', $third->id)->update(['tracking_number' => $first->tracking_number]);
    }

    public function test_year_comes_from_manila_time_and_sequence_does_not_restart_each_year(): void
    {
        $this->travelTo(Carbon::parse('2026-12-31 15:59:59', 'UTC'));
        $first = app(TrackingNumberAllocator::class)->reserve();
        $this->travelTo(Carbon::parse('2026-12-31 16:00:01', 'UTC'));
        $second = app(TrackingNumberAllocator::class)->reserve();
        $this->assertStringStartsWith('DR-2026-', $first['tracking_number']);
        $this->assertStringStartsWith('DR-2027-', $second['tracking_number']);
        $this->assertSame($first['document_number_id'] + 1, $second['document_number_id']);
    }

    public function test_initial_route_failure_rolls_back_document_but_consumes_reserved_number(): void
    {
        DocumentRoute::creating(fn () => throw new \RuntimeException('Simulated route failure'));
        try {
            $this->send();
            $this->fail('Expected failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated route failure', $exception->getMessage());
        } finally {
            DocumentRoute::flushEventListeners();
        }
        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_routes', 0);
        $this->assertDatabaseCount('document_events', 0);
        $reservedId = DB::table('document_numbers')->value('id');
        $this->assertNotNull($reservedId);
        $this->assertGreaterThan($reservedId, $this->send()->document_number_id);
    }

    public function test_initial_audit_failure_also_rolls_back_document_and_assignment(): void
    {
        DocumentEvent::creating(fn () => throw new \RuntimeException('Simulated audit failure'));
        try {
            $this->send();
            $this->fail('Expected failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated audit failure', $exception->getMessage());
        } finally {
            DocumentEvent::flushEventListeners();
            DocumentEvent::clearBootedModels();
        }
        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_routes', 0);
        $this->assertDatabaseCount('document_events', 0);
        $this->assertDatabaseCount('document_numbers', 1);
    }

    public function test_creation_validates_title_department_recipient_and_ignores_forged_sender(): void
    {
        $this->actingAs($this->sender)->post(route('documents.store'), $this->payload(['title' => '', 'recipient_id' => $this->next->id]))->assertSessionHasErrors(['title', 'recipient_id']);
        $this->post(route('documents.store'), $this->payload(['created_by' => $this->admin->id, 'status' => 'closed', 'current_route_id' => 999]))->assertSessionHasNoErrors();
        $this->assertSame($this->sender->id, Document::firstOrFail()->created_by);
        $this->assertSame('pending', Document::firstOrFail()->currentRoute->status);
    }

    public function test_inactive_recipients_and_departments_are_rejected(): void
    {
        $this->recipient->update(['is_active' => false]);
        $this->actingAs($this->sender)->post(route('documents.store'), $this->payload())->assertSessionHasErrors('recipient_id');
        $this->recipient->update(['is_active' => true]);
        $this->destination->update(['is_active' => false]);
        $this->post(route('documents.store'), $this->payload())->assertSessionHasErrors('department_id');
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_recipient_is_revalidated_inside_transaction_after_form_validation(): void
    {
        $this->recipient->update(['is_active' => false]);
        $this->expectException(ValidationException::class);
        $this->send();
    }

    public function test_receive_removes_pending_count_without_closing_or_marking_seen(): void
    {
        $document = $this->send();
        $this->assertSame(1, app(DocumentOverview::class)->forUser($this->recipient)['pending']);
        $this->act($this->recipient, $document, 'receive')->assertSessionHasNoErrors();
        $route = $document->fresh()->currentRoute;
        $this->assertSame('received', $route->status);
        $this->assertNotNull($route->received_at);
        $this->assertNull($route->closed_at);
        $this->assertNull($route->seen_at);
        $counts = app(DocumentOverview::class)->forUser($this->recipient);
        $this->assertSame(0, $counts['pending']);
        $this->assertSame(1, $counts['incoming']);
        $this->assertSame(1, $counts['unseen']);
    }

    public function test_details_mark_only_current_recipient_assignment_seen_once_while_still_pending(): void
    {
        $document = $this->send();
        $this->actingAs($this->recipient)->get(route('documents.show', $document))->assertOk()->assertSee('Tracking history');
        $this->get(route('documents.show', $document))->assertOk();
        $counts = app(DocumentOverview::class)->forUser($this->recipient);
        $this->assertSame(0, $counts['unseen']);
        $this->assertSame(1, $counts['pending']);
        $this->assertSame(1, $document->events()->where('event_type', 'seen')->count());
    }

    public function test_sender_and_monitoring_admin_do_not_mark_recipient_assignment_seen(): void
    {
        $document = $this->send();
        foreach ([$this->sender, $this->admin] as $viewer) {
            $this->actingAs($viewer)->get(route('documents.show', $document))->assertOk();
        }
        $this->assertNull($document->fresh()->currentRoute->seen_at);
        $this->assertSame(0, $document->events()->where('event_type', 'seen')->count());
        $this->actingAs($this->admin)->get(route('documents.index'))->assertOk()->assertSee($document->tracking_number);
    }

    public function test_decline_requires_reason_and_returns_new_unseen_pending_assignment(): void
    {
        $document = $this->send();
        $oldRoute = $document->currentRoute;
        $this->act($this->recipient, $document, 'decline')->assertSessionHasErrors('reason');
        $this->assertSame($oldRoute->id, $document->fresh()->current_route_id);
        $this->act($this->recipient, $document, 'decline', ['reason' => 'Incorrect recipient.'])->assertSessionHasNoErrors();
        $this->assertSame('declined', $oldRoute->fresh()->status);
        $this->assertNotNull($oldRoute->fresh()->declined_at);
        $returned = $document->fresh()->currentRoute;
        $this->assertSame($this->sender->id, $returned->to_user_id);
        $this->assertSame($this->recipient->id, $returned->from_user_id);
        $this->assertSame('pending', $returned->status);
        $this->assertNull($returned->seen_at);
        $this->assertSame(['created', 'sent', 'declined', 'returned'], $document->events()->pluck('event_type')->all());
    }

    public function test_decline_returns_to_forwarder_rather_than_original_creator(): void
    {
        $document = $this->send();
        $this->act($this->recipient, $document, 'receive');
        $this->act($this->recipient, $document, 'forward', ['department_id' => $this->origin->id, 'recipient_id' => $this->next->id])->assertSessionHasNoErrors();
        $this->act($this->next, $document, 'decline', ['reason' => 'Return for clarification'])->assertSessionHasNoErrors();
        $this->assertSame($this->recipient->id, $document->fresh()->currentRoute->to_user_id);
    }

    public function test_decline_to_inactive_or_unavailable_sender_is_atomic(): void
    {
        $document = $this->send();
        $routeId = $document->current_route_id;
        $this->sender->update(['is_active' => false]);
        $this->act($this->recipient, $document, 'decline', ['reason' => 'Return'])->assertSessionHasErrors('reason');
        $this->assertSame('pending', $document->fresh()->currentRoute->status);
        $this->assertSame($routeId, $document->fresh()->current_route_id);
        $this->assertDatabaseCount('document_events', 2);
        $this->assertDatabaseCount('document_routes', 1);
        $this->sender->update(['is_active' => true, 'department_id' => null]);
        $this->act($this->recipient, $document, 'decline', ['reason' => 'Return'])->assertSessionHasErrors('reason');
        $this->sender->update(['department_id' => $this->origin->id]);
        $this->origin->update(['is_active' => false]);
        $this->act($this->recipient, $document, 'decline', ['reason' => 'Return'])->assertSessionHasErrors('reason');
    }

    public function test_forward_requires_receipt_and_preserves_tracking_with_title_audit(): void
    {
        $document = $this->send();
        $tracking = $document->tracking_number;
        $destination = ['department_id' => $this->origin->id, 'recipient_id' => $this->next->id, 'title' => 'Revised memorandum', 'remarks' => 'For action'];
        $this->act($this->recipient, $document, 'forward', $destination)->assertForbidden();
        $this->actingAs($this->recipient)->get(route('documents.show', $document));
        $oldRoute = $document->fresh()->currentRoute;
        $this->act($this->recipient, $document, 'receive');
        $this->act($this->recipient, $document, 'forward', $destination)->assertSessionHasNoErrors();
        $fresh = $document->fresh();
        $this->assertSame($tracking, $fresh->tracking_number);
        $this->assertSame('Revised memorandum', $fresh->title);
        $this->assertSame('forwarded', $oldRoute->fresh()->status);
        $this->assertNotNull($oldRoute->fresh()->seen_at);
        $this->assertNull($fresh->currentRoute->seen_at);
        $this->assertSame($this->next->id, $fresh->currentRoute->to_user_id);
        $change = $document->events()->where('event_type', 'title_changed')->firstOrFail();
        $this->assertSame('Office memorandum', $change->previous_value['title']);
        $this->assertSame('Revised memorandum', $change->new_value['title']);
        $this->assertSame(1, app(DocumentOverview::class)->forUser($this->next)['unseen']);
        $this->assertSame(0, app(DocumentOverview::class)->forUser($this->recipient)['incoming']);
    }

    public function test_forward_without_title_retains_title_and_rejects_inactive_destination(): void
    {
        $document = $this->send();
        $this->act($this->recipient, $document, 'receive');
        $this->next->update(['is_active' => false]);
        $data = ['department_id' => $this->origin->id, 'recipient_id' => $this->next->id];
        $this->act($this->recipient, $document, 'forward', $data)->assertSessionHasErrors('recipient_id');
        $this->assertSame('received', $document->fresh()->currentRoute->status);
        $this->next->update(['is_active' => true]);
        $this->act($this->recipient, $document, 'forward', $data)->assertSessionHasNoErrors();
        $this->assertSame('Office memorandum', $document->fresh()->title);
        $this->assertSame(0, $document->events()->where('event_type', 'title_changed')->count());
    }

    public function test_close_requires_receipt_and_closed_documents_reject_all_actions(): void
    {
        $document = $this->send();
        $this->act($this->recipient, $document, 'close')->assertForbidden();
        $this->act($this->recipient, $document, 'receive');
        $this->act($this->recipient, $document, 'close', ['remarks' => 'Completed review.'])->assertSessionHasNoErrors();
        $route = $document->fresh()->currentRoute;
        $this->assertSame('closed', $route->status);
        $this->assertNotNull($route->closed_at);
        foreach (['receive', 'decline', 'forward', 'close'] as $action) {
            $this->act($this->recipient, $document, $action, ['reason' => 'Return', 'department_id' => $this->origin->id, 'recipient_id' => $this->next->id])->assertForbidden();
        }
        $this->assertSame(1, $document->events()->where('event_type', 'closed')->count());
        $this->assertSame(0, app(DocumentOverview::class)->forUser($this->recipient)['incoming']);
        $this->assertSame(1, app(DocumentOverview::class)->forUser($this->recipient)['completed']);
        $this->assertSame(1, app(DocumentOverview::class)->forUser($this->sender)['completed']);
    }

    public function test_completed_count_deduplicates_creator_who_also_closes(): void
    {
        $document = $this->send();
        $this->act($this->recipient, $document, 'decline', ['reason' => 'Return to originator']);
        $this->act($this->sender, $document, 'receive');
        $this->act($this->sender, $document, 'close');
        $this->assertSame(1, app(DocumentOverview::class)->forUser($this->sender)['completed']);
    }

    public function test_admin_and_other_employees_cannot_take_over_assignment(): void
    {
        $document = $this->send();
        foreach ([$this->sender, $this->next, $this->admin] as $actor) {
            foreach (['receive', 'decline'] as $action) {
                $this->act($actor, $document, $action, ['reason' => 'Unauthorized'])->assertForbidden();
            }
        }
        $this->act($this->recipient, $document, 'receive');
        foreach ([$this->sender, $this->next, $this->admin] as $actor) {
            $this->act($actor, $document, 'forward', ['department_id' => $this->origin->id, 'recipient_id' => $this->next->id])->assertForbidden();
            $this->act($actor, $document, 'close')->assertForbidden();
        }
    }

    public function test_duplicate_and_stale_actions_do_not_create_conflicting_assignments(): void
    {
        $document = $this->send();
        $oldRouteId = $document->current_route_id;
        $this->act($this->recipient, $document, 'receive')->assertSessionHasNoErrors();
        $this->act($this->recipient, $document, 'receive')->assertForbidden();
        $this->act($this->recipient, $document, 'decline', ['reason' => 'Too late'])->assertForbidden();
        $this->act($this->recipient, $document, 'forward', ['department_id' => $this->origin->id, 'recipient_id' => $this->next->id]);
        $this->act($this->next, $document, 'decline', ['reason' => 'Back to forwarder']);
        $this->actingAs($this->recipient)->put(route('documents.receive', $document), ['route_id' => $oldRouteId])->assertSessionHasErrors('route_id');
        $this->assertSame('pending', $document->fresh()->currentRoute->status);
        $this->assertDatabaseCount('document_routes', 3);
        $this->assertSame(1, $document->events()->where('event_type', 'received')->count());
    }

    public function test_previous_participants_keep_view_access_but_lose_action_rights(): void
    {
        $document = $this->send();
        $this->act($this->recipient, $document, 'receive');
        $this->act($this->recipient, $document, 'forward', ['department_id' => $this->origin->id, 'recipient_id' => $this->next->id]);
        $this->actingAs($this->recipient)->get(route('documents.show', $document))->assertOk();
        $this->assertNull($document->fresh()->currentRoute->seen_at);
        $this->act($this->recipient, $document, 'receive')->assertForbidden();
        $this->actingAs($this->recipient)->get(route('documents.index'))->assertSee($document->tracking_number);
    }

    public function test_incoming_outgoing_pending_unseen_and_search_scopes_do_not_leak_documents(): void
    {
        $document = $this->send();
        $this->actingAs($this->recipient)->get(route('documents.incoming'))->assertOk()->assertSee($document->tracking_number);
        $this->get(route('documents.pending'))->assertSee($document->tracking_number);
        $this->get(route('documents.unseen'))->assertSee($document->tracking_number);
        $this->get(route('documents.outgoing'))->assertDontSee($document->tracking_number);
        $this->actingAs($this->sender)->get(route('documents.outgoing'))->assertSee($document->tracking_number);
        $this->get(route('documents.incoming'))->assertDontSee($document->tracking_number);
        $this->actingAs($this->next)->get(route('documents.show', $document))->assertForbidden();
        $this->get(route('documents.index', ['search' => $document->tracking_number]))->assertViewHas('documents', fn ($page) => $page->total() === 0);
        $this->get(route('documents.index', ['search' => $document->title]))->assertViewHas('documents', fn ($page) => $page->total() === 0);
    }

    public function test_filters_pagination_and_recipient_selection_render_correctly(): void
    {
        $document = $this->send();
        $this->actingAs($this->sender)->get(route('documents.create'))->assertOk()->assertSee('Recipient employee')->assertDontSee($this->recipient->email);
        $this->get(route('documents.outgoing', ['status' => 'received']))->assertDontSee($document->tracking_number);
        $this->get(route('documents.outgoing', ['department_id' => $this->origin->id]))->assertDontSee($document->tracking_number);
        $this->get(route('documents.outgoing', ['from' => now('Asia/Manila')->toDateString(), 'to' => now('Asia/Manila')->toDateString()]))->assertSee($document->tracking_number);
        $this->get(route('documents.outgoing', ['to' => now('Asia/Manila')->toDateString()]))->assertOk()->assertSee($document->tracking_number);
        $this->get(route('documents.outgoing', ['from' => '2026-12-31', 'to' => '2026-01-01']))->assertSessionHasErrors('to');
        for ($i = 0; $i < 15; $i++) {
            $this->send();
        }
        $this->get(route('documents.outgoing', ['search' => 'memorandum']))->assertOk()->assertViewHas('documents', fn ($page) => $page->count() === 15 && $page->total() === 16)->assertSee('search=memorandum');
    }

    public function test_dashboard_and_sidebar_share_real_user_specific_counts(): void
    {
        $document = $this->send();
        $this->assertSame(['incoming' => 1, 'outgoing' => 0, 'pending' => 1, 'unseen' => 1, 'completed' => 0], app(DocumentOverview::class)->forUser($this->recipient));
        $this->actingAs($this->recipient)->get(route('dashboard'))->assertOk()->assertViewHas('documentCounts', ['incoming' => 1, 'outgoing' => 0, 'pending' => 1, 'unseen' => 1, 'completed' => 0])->assertSee($document->tracking_number);
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->assertViewHas('documentCounts', ['incoming' => 0, 'outgoing' => 0, 'pending' => 0, 'unseen' => 0, 'completed' => 0])->assertDontSee($document->tracking_number);
    }

    public function test_timeline_uses_id_tiebreaker_and_preserves_identity_snapshots(): void
    {
        $this->freezeTime();
        $document = $this->send();
        $originalName = $this->sender->name;
        $this->sender->update(['name' => 'Renamed Sender']);
        $this->origin->update(['name' => 'Renamed Department']);
        $this->act($this->recipient, $document, 'receive');
        $events = $document->events()->get();
        $this->assertSame(['created', 'sent', 'received'], $events->pluck('event_type')->all());
        $this->assertSame($originalName, $events[0]->context['actor']);
        $this->assertSame('Origin', $events[0]->context['from_department']);
        $this->actingAs($this->sender)->get(route('documents.show', $document))->assertOk()->assertSeeInOrder(['Created</p>', 'Sent</p>', 'Received</p>'], false);
    }

    public function test_events_cannot_be_changed_through_model_operations(): void
    {
        $event = $this->send()->events()->firstOrFail();
        $this->expectException(\LogicException::class);
        $event->update(['remarks' => 'Rewrite history']);
    }

    public function test_events_cannot_be_deleted_through_model_operations(): void
    {
        $event = $this->send()->events()->firstOrFail();
        $this->expectException(\LogicException::class);
        $event->delete();
    }

    public function test_accounts_with_document_history_cannot_be_deleted(): void
    {
        $this->send();
        $this->actingAs($this->sender)->delete(route('profile.destroy'), ['password' => 'password'])->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull($this->sender->fresh());
    }

    public function test_guest_and_inactive_users_cannot_use_document_routes(): void
    {
        $document = $this->send();
        $this->get(route('documents.index'))->assertRedirect(route('login'));
        $this->post(route('documents.store'), $this->payload())->assertRedirect(route('login'));
        $this->get(route('documents.show', $document))->assertRedirect(route('login'));
        $this->recipient->update(['is_active' => false]);
        $this->act($this->recipient, $document, 'receive')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertSame('pending', $document->fresh()->currentRoute->status);
    }

    public function test_forward_failure_rolls_back_title_assignment_and_audit_changes(): void
    {
        $document = $this->send();
        $this->act($this->recipient, $document, 'receive');
        DocumentRoute::creating(fn () => throw new \RuntimeException('Forward assignment failed'));
        try {
            app(DocumentWorkflow::class)->transition($this->recipient, $document, 'forward', [
                'route_id' => $document->current_route_id, 'department_id' => $this->origin->id,
                'recipient_id' => $this->next->id, 'title' => 'Should roll back',
            ]);
            $this->fail('Expected assignment failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Forward assignment failed', $exception->getMessage());
        } finally {
            DocumentRoute::flushEventListeners();
        }
        $this->assertSame('Office memorandum', $document->fresh()->title);
        $this->assertSame('received', $document->fresh()->currentRoute->status);
        $this->assertDatabaseCount('document_routes', 1);
        $this->assertSame(0, $document->events()->whereIn('event_type', ['title_changed', 'forwarded'])->count());
    }

    public function test_returned_assignment_has_independent_seen_state_for_same_recipient(): void
    {
        $document = $this->send();
        $this->actingAs($this->recipient)->get(route('documents.show', $document));
        $firstRoute = $document->fresh()->currentRoute;
        $this->act($this->recipient, $document, 'receive');
        $this->act($this->recipient, $document, 'forward', ['department_id' => $this->origin->id, 'recipient_id' => $this->next->id]);
        $this->act($this->next, $document, 'decline', ['reason' => 'Return']);
        $this->assertNotNull($firstRoute->fresh()->seen_at);
        $this->assertNull($document->fresh()->currentRoute->seen_at);
        $this->assertSame(1, app(DocumentOverview::class)->forUser($this->recipient)['unseen']);
    }

    public function test_destination_department_and_actor_are_revalidated_by_workflow_service(): void
    {
        $document = $this->send();
        $this->act($this->recipient, $document, 'receive');
        $this->origin->update(['is_active' => false]);
        try {
            app(DocumentWorkflow::class)->transition($this->recipient, $document, 'forward', [
                'route_id' => $document->current_route_id, 'department_id' => $this->origin->id, 'recipient_id' => $this->next->id,
            ]);
            $this->fail('Expected inactive department rejection');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('recipient_id', $exception->errors());
        }
        User::whereKey($this->recipient->id)->update(['is_active' => false]);
        try {
            app(DocumentWorkflow::class)->transition($this->recipient, $document, 'close', ['route_id' => $document->current_route_id]);
            $this->fail('Expected inactive actor rejection');
        } catch (AuthorizationException $exception) {
            $this->assertNotNull($exception);
        }
        $this->assertSame('received', $document->fresh()->currentRoute->status);
    }

    public function test_all_document_endpoints_keep_required_middleware(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            if (str_starts_with($route->getName() ?? '', 'documents.')) {
                foreach (['auth', 'verified', 'active'] as $middleware) {
                    $this->assertContains($middleware, $route->gatherMiddleware());
                }
            }
        }
    }
}
