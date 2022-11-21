@inject('storageService', 'App\Services\StorageService')

<div @class([$uniqueClass, 'columns is-multiline'])>
    @if (!$posts->isEmpty())
        @foreach ($posts as $post)
            <div class="column is-4">
                <article class="b752-blog-item box is-clipped p-0">
                    <figure>
                        <a href="{{ route('blog.show', $post->slug) }}">
                            <img src="{{ $post->getOptimizedCoverImageUrl(600, 400) ?? $storageService::getImageUrl(config('constants.default_images.post_thumbnail')) }}">
                        </a>
                    </figure>
                    <div class="p-5">
                        <h2 class="title is-5 mb-2">
                            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                        </h2>
                        <div class="content is-size-7">
                            <p>{{ $post->getCategoryNames() }}</p>
                        </div>
                    </div>
                </article>
            </div>
        @endforeach
    @else
        <div class="column">
            <p>Empty posts.</p>
        </div>
    @endif
</div>