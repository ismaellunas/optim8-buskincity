@inject('storageService', 'App\Services\StorageService')

<div class="column is-4-desktop is-6-tablet is-12-mobile">
    <article class="b752-blog-item box is-clipped p-0">
        <figure>
            <a href="{{ $link }}">
                <img
                    data-src="{{ $post->getOptimizedCoverImageUrl(600, 400) ?? $storageService->getImageUrl(config('constants.default_images.post_thumbnail')) }}"
                    class="lazyload"
                >
            </a>
        </figure>
        <div class="p-5">
            <h2 class="title is-5 mb-2">
                <a href="{{ $link }}">{{ $post->title }}</a>
            </h2>
            <div class="content is-size-7">
                <p>{{ $post->getCategoryNames() }}</p>
            </div>
        </div>
    </article>
</div>
