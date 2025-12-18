{{-- resources/views/components/delete-modal.blade.php --}}
@props([
    'id' => 'deleteModal',
    'title' => 'Confirm Delete',
    'message' => 'Are you sure you want to delete this item? This action cannot be undone.'
])

<div class="modal fade" id="{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>{{ $title }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">{{ $message }}</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="{{ $id }}-form" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('{{ $id }}').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const action = button.getAttribute('data-action');
    document.getElementById('{{ $id }}-form').setAttribute('action', action);
});
</script>
@endpush