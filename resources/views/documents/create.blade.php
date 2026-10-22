@extends('layouts.docroute')
@section('title', 'Create Document')
@section('content')
<h1 class="mb-2 text-2xl font-bold">Create document</h1><p class="mb-6 text-sm text-slate-500">Send a document to an active PALECO recipient. Attachments will be available in the next phase.</p>
<form method="POST" action="{{ route('documents.store') }}" class="max-w-3xl space-y-6 rounded-xl border border-slate-200 bg-white p-6">@csrf
<div><label for="title" class="block text-sm font-medium">Document title</label><input id="title" name="title" value="{{ old('title') }}" required maxlength="255" class="mt-2 w-full rounded-lg border-slate-300"></div>
<div><label for="description" class="block text-sm font-medium">Description (optional)</label><textarea id="description" name="description" rows="4" maxlength="10000" class="mt-2 w-full rounded-lg border-slate-300">{{ old('description') }}</textarea></div>
@include('documents.destination-fields')
<div><label for="remarks" class="block text-sm font-medium">Sending remarks (optional)</label><textarea id="remarks" name="remarks" rows="3" maxlength="5000" class="mt-2 w-full rounded-lg border-slate-300">{{ old('remarks') }}</textarea></div>
<div class="flex flex-wrap gap-3"><button class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Send document</button><a href="{{ route('documents.outgoing') }}" class="rounded-lg border px-5 py-3 text-sm">Cancel</a></div>
</form>
@endsection
