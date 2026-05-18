<div class="form-group {{ $class ?? '' }}">
    @if($type === 'textarea')
        <label for="{{ $name }}">{{ $label }}</label>
        <textarea 
            id="{{ $name }}" 
            name="{{ $name }}" 
            placeholder="{{ $placeholder ?? '' }}"
            {{ ($disabled ?? false) ? 'disabled' : '' }}
        >{{ old($name, $value ?? '') }}</textarea>
    @elseif($type === 'select')
        <label for="{{ $name }}">{{ $label }}</label>
        <select 
            id="{{ $name }}" 
            name="{{ $name }}" 
            {{ ($disabled ?? false) ? 'disabled' : '' }}
            {{ ($required ?? false) ? 'required' : '' }}
        >
            <option value="">-- Pilih {{ $label }} --</option>
            @foreach($options ?? [] as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @else
        <label for="{{ $name }}">{{ $label }}</label>
        <input 
            type="{{ $type ?? 'text' }}" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            placeholder="{{ $placeholder ?? '' }}"
            value="{{ old($name, $value ?? '') }}"
            {{ ($disabled ?? false) ? 'disabled' : '' }}
            {{ ($required ?? false) ? 'required' : '' }}
        />
    @endif

    @if($errors->has($name))
        <div class="form-error">
            {{ $errors->first($name) }}
        </div>
    @endif
</div>
