<?php

namespace App\Http\Controllers;

use App\Http\Requests\Documents\DocumentActionRequest;
use App\Http\Requests\Documents\SendDocumentRequest;
use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use App\Services\DocumentWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request, string $folder = 'all'): View
    {
        Gate::authorize('viewAny', Document::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:pending,received,closed'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => array_merge(['nullable', 'date_format:Y-m-d'], $request->filled('from') ? ['after_or_equal:from'] : []),
        ]);
        $query = Document::visibleTo($request->user())->with(['creator', 'currentRoute.sender', 'currentRoute.recipient', 'currentRoute.fromDepartment', 'currentRoute.toDepartment']);
        if (in_array($folder, ['incoming', 'pending', 'unseen'], true)) {
            $query->assignedTo($request->user());
        } elseif ($folder === 'outgoing') {
            $query->where('created_by', $request->user()->id);
        }
        if ($folder === 'pending') {
            $query->whereHas('currentRoute', fn ($route) => $route->where('status', 'pending'));
        } elseif ($folder === 'unseen') {
            $query->whereHas('currentRoute', fn ($route) => $route->whereNull('seen_at'));
        }
        if (! empty($filters['search'])) {
            $query->where(fn ($query) => $query->where('tracking_number', 'like', '%'.$filters['search'].'%')->orWhere('title', 'like', '%'.$filters['search'].'%'));
        }
        if (! empty($filters['status'])) {
            $query->whereHas('currentRoute', fn ($route) => $route->where('status', $filters['status']));
        }
        if (! empty($filters['department_id'])) {
            $query->whereHas('currentRoute', fn ($route) => $route->where('to_department_id', $filters['department_id']));
        }
        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['from'], 'Asia/Manila')->startOfDay()->utc());
        }
        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['to'], 'Asia/Manila')->endOfDay()->utc());
        }

        return view('documents.index', [
            'documents' => $query->latest('updated_at')->orderByDesc('id')->paginate(15)->withQueryString(),
            'departments' => Department::orderBy('name')->get(['id', 'code', 'name']), 'folder' => $folder,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Document::class);

        return view('documents.create', $this->destinations());
    }

    public function store(SendDocumentRequest $request, DocumentWorkflow $workflow): RedirectResponse
    {
        $document = $workflow->create($request->user(), $request->validated());

        return to_route('documents.show', $document)->with('success', 'Document sent. Tracking number: '.$document->tracking_number);
    }

    public function show(Request $request, Document $document, DocumentWorkflow $workflow): View
    {
        $document = $workflow->view($request->user(), $document);
        $document->load(['creator', 'currentRoute.sender', 'currentRoute.recipient', 'currentRoute.fromDepartment', 'currentRoute.toDepartment', 'events']);

        return view('documents.show', ['document' => $document] + (Gate::allows('forward', $document) ? $this->destinations() : []));
    }

    public function receive(DocumentActionRequest $request, Document $document, DocumentWorkflow $workflow): RedirectResponse
    {
        return $this->act($request, $document, $workflow, 'receive');
    }

    public function decline(DocumentActionRequest $request, Document $document, DocumentWorkflow $workflow): RedirectResponse
    {
        return $this->act($request, $document, $workflow, 'decline');
    }

    public function forward(SendDocumentRequest $request, Document $document, DocumentWorkflow $workflow): RedirectResponse
    {
        $workflow->transition($request->user(), $document, 'forward', $request->validated());

        return to_route('documents.show', $document)->with('success', 'Document forwarded.');
    }

    public function close(DocumentActionRequest $request, Document $document, DocumentWorkflow $workflow): RedirectResponse
    {
        return $this->act($request, $document, $workflow, 'close');
    }

    private function act(DocumentActionRequest $request, Document $document, DocumentWorkflow $workflow, string $action): RedirectResponse
    {
        $workflow->transition($request->user(), $document, $action, $request->validated());

        return to_route('documents.show', $document)->with('success', match ($action) {
            'receive' => 'Document received.',
            'decline' => 'Document declined and returned to the previous sender.',
            'close' => 'Document closed.',
        });
    }

    private function destinations(): array
    {
        return [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'recipients' => User::where('is_active', true)->whereHas('department', fn ($query) => $query->where('is_active', true))->orderBy('name')->get(['id', 'name', 'department_id']),
        ];
    }
}
