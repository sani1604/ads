{{-- resources/views/components/pagination.blade.php --}}
@props(['paginator'])

@if($paginator->hasPages())
    <nav aria-label="Page navigation" class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="text-muted small">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </div>
        
        <ul class="pagination pagination-sm mb-0">
            {{-- Previous --}}
            @if($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
                </li>
            @endif

            {{-- Page Numbers --}}
            @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if($page == $paginator->currentPage())
                    <li class="page-item active">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            {{-- Next --}}
            @if($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif