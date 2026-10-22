<section aria-labelledby="documents-heading" class="mt-9">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2"><h2 id="documents-heading" class="text-base font-semibold text-slate-900">Your Document Overview</h2><a href="{{ route('documents.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-blue-700">View documents</a></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach(['incoming' => 'Incoming Documents', 'outgoing' => 'Outgoing Documents', 'pending' => 'Pending Documents', 'completed' => 'Completed Documents'] as $key => $label)
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><x-docroute-icon :name="$key" class="h-6 w-6 text-blue-600" /><p class="mt-5 text-3xl font-bold tabular-nums text-slate-900">{{ number_format($documentCounts[$key]) }}</p><h3 class="mt-1 text-sm text-slate-500">{{ $label }}</h3></article>
        @endforeach
    </div><p class="mt-3 text-xs leading-5 text-slate-500">Completed includes closed documents you created or closed yourself, counted once. Seen documents may still be pending.</p>
</section>
