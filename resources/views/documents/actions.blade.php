@php($route = $document->currentRoute)
<div class="flex flex-wrap items-start gap-2">
@can('receive', $document)
<form method="POST" action="{{ route('documents.receive', $document) }}">@csrf @method('PUT')<input type="hidden" name="route_id" value="{{ $route->id }}"><button class="rounded-lg bg-blue-600 px-4 py-3 text-sm font-medium text-white">Receive</button></form>
<details class="max-w-sm"><summary class="cursor-pointer rounded-lg border border-red-200 px-4 py-3 text-sm font-medium text-red-700">Decline</summary><form method="POST" action="{{ route('documents.decline', $document) }}" class="mt-3 space-y-3" x-data @submit="if (!confirm('Decline and return this document to its previous sender?')) $event.preventDefault()">@csrf @method('PUT')<input type="hidden" name="route_id" value="{{ $route->id }}"><label for="reason-{{ $document->id }}" class="block text-sm">Decline reason</label><textarea id="reason-{{ $document->id }}" name="reason" required maxlength="5000" rows="3" class="w-full rounded-lg border-slate-300">{{ old('reason') }}</textarea><button class="rounded-lg bg-red-700 px-4 py-3 text-sm text-white">Confirm decline</button></form></details>
@endcan
@can('forward', $document)<a href="{{ route('documents.show', $document) }}#forward" class="rounded-lg bg-blue-600 px-4 py-3 text-sm text-white">Forward</a>@endcan
@can('close', $document)
<details class="max-w-sm"><summary class="cursor-pointer rounded-lg border border-slate-300 px-4 py-3 text-sm font-medium">Close document</summary><form method="POST" action="{{ route('documents.close', $document) }}" class="mt-3 space-y-3" x-data @submit="if (!confirm('Close this document? It cannot be routed again.')) $event.preventDefault()">@csrf @method('PUT')<input type="hidden" name="route_id" value="{{ $route->id }}"><label for="closing-{{ $document->id }}" class="block text-sm">Closing remarks (optional)</label><textarea id="closing-{{ $document->id }}" name="remarks" maxlength="5000" rows="3" class="w-full rounded-lg border-slate-300">{{ old('remarks') }}</textarea><button class="rounded-lg bg-slate-900 px-4 py-3 text-sm text-white">Confirm close</button></form></details>
@endcan
</div>
