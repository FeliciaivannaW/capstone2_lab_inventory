@props(['field' => ''])

<th field="{{ $field }}"
    {{ $attributes->merge(['class' => 'sortable group border-b border-slate-200 bg-slate-50/50']) }}
    @click="sortBy('{{ $field }}', $el)"
    :class="{ 
        'asc': sortField === '{{ $field }}' && sortAsc, 
        'desc': sortField === '{{ $field }}' && !sortAsc 
    }">
    <div class="flex items-center gap-1.5 cursor-pointer select-none">
        <span class="font-semibold text-slate-600 text-[0.7rem] uppercase tracking-wider">{{ $slot }}</span>
        <span class="inline-flex flex-col text-slate-300 group-hover:text-slate-400 transition-colors">
            <svg class="w-2 h-2 transition-colors" :class="{ 'text-indigo-600': sortField === '{{ $field }}' && sortAsc }" fill="currentColor" viewBox="0 0 24 24">
                <path d="M4 15l8-8 8 8H4z"/>
            </svg>
            <svg class="w-2 h-2 mt-0.5 transition-colors" :class="{ 'text-indigo-600': sortField === '{{ $field }}' && !sortAsc }" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20 9l-8 8-8-8h16z"/>
            </svg>
        </span>
    </div>
</th>
