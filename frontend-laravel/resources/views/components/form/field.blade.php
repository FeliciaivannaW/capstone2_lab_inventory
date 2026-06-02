@props(['label', 'name', 'type' => 'text', 'placeholder' => '', 'value' => '', 'class' => 'mb-4', 'disabled' => false, 'required' => false, 'rows' => 3, 'options' => []])
<div class="{{ $class }}">
    <label for="{{ $name }}" class="block text-xs font-semibold text-slate-600 mb-1">
        {{ $label }}
        @if($required ?? false)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @if(($type ?? 'text') === 'textarea')
        <textarea 
            id="{{ $name }}" 
            name="{{ $name }}" 
            placeholder="{{ $placeholder ?? '' }}"
            class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all {{ $errors->has($name) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : '' }}"
            {{ ($disabled ?? false) ? 'disabled' : '' }}
            {{ ($required ?? false) ? 'required' : '' }}
            rows="{{ $rows ?? 3 }}"
            {{ $attributes }}
        >{{ old($name, $value ?? '') }}</textarea>
    @elseif(($type ?? 'text') === 'select')
        <select 
            id="{{ $name }}" 
            name="{{ $name }}" 
            class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all {{ $errors->has($name) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : '' }}"
            {{ ($disabled ?? false) ? 'disabled' : '' }}
            {{ ($required ?? false) ? 'required' : '' }}
            {{ $attributes }}
        >
            <option value="" disabled {{ old($name, $value ?? '') == '' ? 'selected' : '' }}>-- Pilih {{ $label }} --</option>
            @foreach($options ?? [] as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" {{ old($name, $value ?? '') == $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @elseif(($type ?? 'text') === 'password')
        <div x-data="{ show: false }" class="relative">
            <input 
                x-bind:type="show ? 'text' : 'password'" 
                id="{{ $name }}" 
                name="{{ $name }}" 
                placeholder="{{ $placeholder ?? '' }}"
                value="{{ old($name, $value ?? '') }}"
                class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all pr-10 {{ $errors->has($name) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : '' }}"
                {{ ($disabled ?? false) ? 'disabled' : '' }}
                {{ ($required ?? false) ? 'required' : '' }}
                {{ $attributes }}
            />
            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 focus:outline-none transition-colors">
                <!-- Eye icon (Show) -->
                <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <!-- Eye Slash icon (Hide) -->
                <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
            </button>
        </div>
    @else
        <input 
            type="{{ $type ?? 'text' }}" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            placeholder="{{ $placeholder ?? '' }}"
            value="{{ old($name, $value ?? '') }}"
            class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all {{ $errors->has($name) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : '' }}"
            {{ ($disabled ?? false) ? 'disabled' : '' }}
            {{ ($required ?? false) ? 'required' : '' }}
            {{ $attributes }}
        />
    @endif

    @if($errors->has($name))
        <div class="text-xs text-red-500 mt-1 font-medium">
            {{ $errors->first($name) }}
        </div>
    @endif
</div>
