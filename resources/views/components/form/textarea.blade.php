{{-- resources/views/components/form/textarea.blade.php --}}
@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => '',
    'rows' => 4,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => null,
    'maxlength' => null
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
    
    <textarea 
        class="form-control @error($name) is-invalid @enderror" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        {{ $maxlength ? "maxlength=$maxlength" : '' }}
        {{ $attributes }}
    >{{ old($name, $value) }}</textarea>
    
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    
    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    
    @if($maxlength)
        <div class="form-text text-end">
            <span id="{{ $name }}-count">0</span>/{{ $maxlength }}
        </div>
        @push('scripts')
        <script>
            document.getElementById('{{ $name }}').addEventListener('input', function() {
                document.getElementById('{{ $name }}-count').textContent = this.value.length;
            });
        </script>
        @endpush
    @endif
</div>