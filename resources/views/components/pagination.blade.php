<div>
    @if ($paginator->hasPages())
        <nav
            class="pagination is-centered"
            role="navigation"
            aria-label="pagination"
        >
            <a
                class="pagination-previous"
                href="{{ $paginator->previousPageUrl().$serializedParams }}"
                @if ($paginator->onFirstPage()) disabled @endif
            >
                « {{ __('Previous') }}
            </a>
            <a
                class="pagination-next"
                href="{{ $paginator->nextPageUrl().$serializedParams }}"
                @unless ($paginator->hasMorePages()) disabled @endunless
            >
                {{ __('Next') }} »
            </a>

            <ul class="pagination-list">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="pagination-ellipsis">
                            <span>{{ $element }}</span>
                        </li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li>
                                <a
                                    href="{{ $url.$serializedParams }}"
                                    @class([
                                        'pagination-link',
                                        'is-current' => ($page == $paginator->currentPage())
                                    ])
                                >
                                    {{ $page }}
                                </a>
                            </li>
                        @endforeach
                    @endif
                @endforeach
            </ul>
        </nav>
    @endif
</div>