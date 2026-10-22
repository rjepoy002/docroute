@props(['status'])
<span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ match($status) { 'pending' => 'bg-amber-50 text-amber-800', 'received' => 'bg-blue-50 text-blue-700', 'closed' => 'bg-emerald-50 text-emerald-700', default => 'bg-slate-100 text-slate-600' } }}">{{ ucfirst($status) }}</span>
