<div id="{{ $id }}" {{ $attributes->merge(['class' => 'modal']) }}>
    <div class="modal-background"></div>
    {{ $slot }}
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