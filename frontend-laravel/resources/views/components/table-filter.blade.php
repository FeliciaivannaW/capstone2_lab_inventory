@props(['column', 'label' => '', 'options' => []])

<div class="flex flex-col gap-1" {{ $attributes }}>
    @if($label)
        <label class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider">{{ $label }}</label>
    @endif
    <select :value="filters['{{ $column }}'] || ''"
            @change="setFilter('{{ $column }}', $el.value)" 
            class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 min-w-[130px] shadow-sm cursor-pointer hover:border-slate-300 transition-colors">
        <option value="">Semua {{ $label ?: $column }}</option>
        @foreach($options as $val => $text)
            <option value="{{ $val }}">{{ $text }}</option>
        @endforeach
    </select>
</div>
