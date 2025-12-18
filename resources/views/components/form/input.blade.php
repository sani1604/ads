{{-- resources/views/components/form/input.blade.php --}}
@props([
    'type' => 'text',
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => null,
    'prepend' => null,
    'append' => null
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
    
    @if($prepend || $append)
        <div class="input-group">
            @if($prepend)
                <span class="input-group-text">{!! $prepend !!}</span>
            @endif
            <input 
                type="{{ $type }}" 
                class="form-control @error($name) is-invalid @enderror" 
                id="{{ $name }}" 
                name="{{ $name }}" 
                value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder }}"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $readonly ? 'readonly' : '' }}
                {{ $attributes }}
            >
            @if($append)
                <span class="input-group-text">{!! $append !!}</span>
            @endif
            @error($name)
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @else
        <input 
            type="{{ $type }}" 
            class="form-control @error($name) is-invalid @enderror" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $attributes }}
        >
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    @endif
    
    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>