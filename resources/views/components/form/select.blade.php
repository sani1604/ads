{{-- resources/views/components/form/select.blade.php --}}
@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select an option',
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'searchable' => false,
    'help' => null
])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    <select 
        class="form-select @if($searchable) select2 @endif @error($name) is-invalid @enderror" 
        id="{{ $name }}" 
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        {{ $attributes }}
    >
        @if($placeholder && !$multiple)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @foreach($options as $value => $optionLabel)
            @php
                $isSelected = $multiple 
                    ? in_array($value, (array) old($name, $selected ?? [])) 
                    : old($name, $selected) == $value;
            @endphp
            <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
    
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    
    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>