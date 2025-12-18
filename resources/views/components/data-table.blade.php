{{-- resources/views/components/data-table.blade.php --}}
@props([
    'id' => 'dataTable',
    'columns' => [],
    'searchable' => true,
    'exportable' => false,
    'exportUrl' => null
])

<div class="card">
    @if($searchable || $exportable)
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                @if($searchable)
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control" placeholder="Search..." id="{{ $id }}-search">
                    </div>
                @endif
                
                @if($exportable && $exportUrl)
                    <a href="{{ $exportUrl }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-1"></i> Export CSV
                    </a>
                @endif
            </div>
        </div>
    @endif
    
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="{{ $id }}">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th class="{{ $column['class'] ?? '' }}" style="{{ $column['style'] ?? '' }}">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

@if($searchable)
@push('scripts')
<script>
document.getElementById('{{ $id }}-search').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const table = document.getElementById('{{ $id }}');
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});
</script>
@endpush
@endif