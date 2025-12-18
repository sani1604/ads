{{-- resources/views/components/form/file.blade.php --}}
@props([
    'name',
    'label' => null,
    'accept' => null,
    'multiple' => false,
    'required' => false,
    'help' => null,
    'preview' => false,
    'currentFile' => null
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
    
    @if($currentFile)
        <div class="mb-2">
            <span class="badge bg-light text-dark">
                <i class="fas fa-file me-1"></i>
                Current: {{ is_string($currentFile) ? basename($currentFile) : 'File uploaded' }}
            </span>
        </div>
    @endif
    
    <input 
        type="file" 
        class="form-control @error($name) is-invalid @enderror" 
        id="{{ $name }}" 
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        {{ $accept ? "accept=$accept" : '' }}
        {{ $multiple ? 'multiple' : '' }}
        {{ $required ? 'required' : '' }}
        {{ $attributes }}
    >
    
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    
    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    
    @if($preview)
        <div id="{{ $name }}-preview" class="mt-2 d-flex flex-wrap gap-2"></div>
        
        @push('scripts')
        <script>
            document.getElementById('{{ $name }}').addEventListener('change', function(e) {
                const preview = document.getElementById('{{ $name }}-preview');
                preview.innerHTML = '';
                
                Array.from(this.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'rounded';
                            img.style.height = '80px';
                            img.style.objectFit = 'cover';
                            preview.appendChild(img);
                        }
                        reader.readAsDataURL(file);
                    } else {
                        const span = document.createElement('span');
                        span.className = 'badge bg-secondary';
                        span.innerHTML = '<i class="fas fa-file me-1"></i>' + file.name;
                        preview.appendChild(span);
                    }
                });
            });
        </script>
        @endpush
    @endif
</div>