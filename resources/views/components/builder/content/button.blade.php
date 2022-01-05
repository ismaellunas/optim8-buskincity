<a
    id="{{ 'button-'.$entity['id'] }}"
    href="{{ $link }}"
    target="{{ $target }}"
    @class(array_merge(['button'], $buttonClasses))
>
    @if (
        ($iconPosition === null || $iconPosition === 'left')
        && $buttonContent['icon']
    )
        <span class="icon">
            <i @class($buttonContent['icon'])></i>
        </span>
    @endif

    <span>
        {{ $buttonContent['text'] }}
    </span>

    @if ($iconPosition === 'right' && $buttonContent['icon'])
        <span class="icon">
            <i @class($buttonContent['icon'])></i>
        </span>
    @endif
</a>

@if ($isDownload)
    @push('bottom_scripts')
        <script>
            window.addEventListener('DOMContentLoaded', init);

            function init(){
                document.getElementById("{{ 'button-'.$entity['id'] }}").addEventListener("click", function (e) {
                    e.preventDefault();

                    let url = "{{ $link }}";

                    new JsFileDownloader({
                        url: url,
                        nativeFallbackOnError: true
                    })
                });
            }
        </script>
    @endpush
@endif