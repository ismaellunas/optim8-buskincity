<div @class([$uniqueClass, 'pb-latest-post columns is-multiline'])>
    @if (!$posts->isEmpty())
        @for ($i = 0; $i < $limit; $i++)
            <div class="column is-4-desktop is-6-tablet is-12-mobile">
                @isset($posts[$i])
                    <article class="b752-blog-item box is-clipped p-0">
                        <figure class="image">
                            <a href="{{ route('blog.show', $posts[$i]->slug) }}">
                                <x-image
                                    src="{{ $posts[$i]->getOptimizedThumbnailOrDefaultUrl() }}"
                                    width="{{ config('constants.dimensions.post_thumbnail.width') }}"
                                    height="{{ config('constants.dimensions.post_thumbnail.height') }}"
                                    is-lazyload
                                />
                            </a>
                        </figure>
                        <div class="p-5">
                            <h2 class="title is-5 mb-2">
                                <a href="{{ route('blog.show', $posts[$i]->slug) }}">{{ $posts[$i]->title }}</a>
                            </h2>
                            <div class="content is-size-7">
                                <p>{{ $posts[$i]->getCategoryNames() }}</p>
                            </div>
                        </div>
                    </article>
                @endisset
            </div>
        @endfor
    @else
        <div class="column">
            <p>Empty posts.</p>
        </div>
    @endif
</div>
