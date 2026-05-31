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
