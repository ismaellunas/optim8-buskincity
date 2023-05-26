<div @class(array_merge([$uniqueClass], $wrapperClasses))>
    <a
        id="{{ 'button-'.$entity['id'] }}"
        href="{{ $link }}"
        target="{{ $target }}"
        @class(array_merge(['button'], $buttonClasses))
    >
        @if (
            ($icon['position'] === null || $icon['position'] === 'left')
            && $icon['class']
        )
            <span class="icon">
                <i @class($icon['class'])></i>
            </span>
        @endif

        <span @class($textClasses)>
            {{ $buttonContent['text'] }}
        </span>

        @if ($icon['position'] === 'right' && $icon['class'])
            <span class="icon">
                <i @class($icon['class'])></i>
            </span>
        @endif
    </a>

    <div class="is-clearfix"></div>
</div>

@if ($isDownload)
    @push('bottom_scripts')
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                document.getElementById("{{ 'button-'.$entity['id'] }}").addEventListener("click", function (e) {
                    e.preventDefault();

                    let url = "{{ $link }}";

                    new JsFileDownloader({
                        url: url,
                        nativeFallbackOnError: true
                    })
                });
            });
        </script>
    @endpush
@endif