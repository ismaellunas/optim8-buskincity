<div id="{{ $id }}" class="modal">
    <div class="modal-background"></div>
    <div class="modal-content {{ $modalContentClass }}">
        <div class="card">
            <div class="card-content">
                {{ $slot }}
            </div>
        </div>
    </div>
    <button class="modal-close is-large" aria-label="close" onclick="modalClose()"></button>
</div>

@push('bottom_scripts')
<script>
    function modalClose() {
        let modal = document.querySelectorAll('.modal');

        modal.forEach(function(el) {
            if (el.classList.contains('is-active')) {
                el.classList.remove('is-active');
            }
        });
    }
</script>
@endpush