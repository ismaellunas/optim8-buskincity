@inject('storageService', 'App\Services\StorageService')

<x-layouts.post>
    <x-slot name="title">
        {{ trim($post->meta_title ?? $post->title). ' | ' .config('app.name') }}
    </x-slot>

    <x-slot name="metaDescription">
        {{ $post->meta_description }}
    </x-slot>

    <div class="b752-blog-post section is-medium">
        <div class="container">
            <div class="columns is-centered is-multiline is-mobile">
                <div class="column is-7-desktop is-7-tablet is-12-mobile">
                    <header>
                        <h1 class="title is-1 is-hidden-mobile">{{ $post->title }}</h1>
                        <h1 class="title is-2 is-hidden-tablet">{{ $post->title }}</h1>

                        <div class="is-flex">
                            <nav class="breadcrumb">
                                <ul>
                                    <li>
                                        <a href="{{ route('homepage') }}">
                                            {{ __('Home') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('blog.index') }}">
                                            {{ __('Blog') }}
                                        </a>
                                    </li>
                                    @if ($post->category)
                                        <li class="is-active">
                                            <a href="#">
                                                {{ $post->category->name }}
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                            <div>
                                <span class="mr-1">•</span> {{ $readingTime }} {{ __('Minute Read') }}
                            </div>
                        </div>
                    </header>

                    <div id="post-content" class="content mt-5">
                        @if (
                            ! empty($post->coverImageWithDimension)
                            && $post->is_cover_displayed
                        )
                            <figure class="image">
                                <x-image
                                    src="{{ $post->coverImageWithDimension['url'] }}"
                                    alt="{{ $post->meta_description }}"
                                    width="{{ $post->coverImageWithDimension['width'] }}"
                                    height="{{ $post->coverImageWithDimension['height'] }}"
                                    is-lazyload
                                />
                            </figure>
                        @endif

                        {!! Shortcode::compile($content) !!}
                    </div>

                    <div class="tags mt-6">
                        @if ($publishedOn)
                            <span class="tag">{{ $publishedOn }}</span>
                        @endif

                        <span class="tag">{{ $lastUpdatedOn }}</span>
                    </div>

                    @if ($post->author)
                        <div class="box is-shadowless has-background-light mt-6">
                            <div class="is-flex is-align-items-center">
                                <div class="pr-2">
                                    <figure class="image is-96x96 mr-3">
                                        <img
                                            width="96"
                                            height="96"
                                            src="{{ $post->author->optimizedProfilePhotoOrDefaultUrl }}"
                                            class="is-rounded ls-is-cached lazyloaded"
                                            alt="Author: {{ $post->author->fullName }}"
                                        >
                                    </figure>
                                </div>
                                <div>
                                    <div class="is-flex is-align-items-center mb-3">
                                        <h3 class="title is-5 m-0">
                                            {{ $post->author->fullName }}
                                        </h3>
                                        <p class="is-size-7 is-hidden-touch ml-3">
                                            {{ __('Author') }}
                                        </p>
                                    </div>
                                    <p class="is-size-7 is-hidden-desktop">
                                        {{ __('Author') }}
                                    </p>
                                    <p>
                                        {{ $post->author->getTranslatableMeta('short_description', $currentLanguage) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="column is-3-desktop is-3-tablet is-12-mobile is-offset-1">
                    <div class="b752-blog-sidebar">
                        <aside class="menu">
                            <p class="menu-label">Table of Contents</p>
                            <ul class="menu-list">
                                @foreach ($tableOfContents as $content)
                                    <li>
                                        <a href="{{ $content['tag'] }}">
                                            {!! $content['text'] !!}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (!$relatedArticles->isEmpty())
        <div class="section is-medium has-background-light">
            <div class="container">
                <div class="columns is-multiline is-mobile">
                    <div class="column is-12-desktop is-12-tablet is-12-mobile">
                        <h2 class="title is-2 mb-5">{{ __('Related Articles') }}</h2>
                    </div>

                    @foreach ($relatedArticles as $article)
                        <div class="column is-4-desktop is-6-tablet is-12-mobile">
                            <article class="b752-blog-item box is-shadowless is-clipped p-0">
                                <figure>
                                    <a href="{{ route('blog.show', $article->slug) }}">
                                        <x-image
                                            src="{{ $article->getOptimizedCoverImageUrl(600, 400) ?? $storageService::getImageUrl(config('constants.default_images.post_thumbnail')) }}"
                                            width="600"
                                            height="400"
                                            is-lazyload
                                        />
                                    </a>
                                </figure>
                                <div class="p-5">
                                    <h2 class="title is-5 mb-2">
                                        <a href="{{ route('blog.show', $article->slug) }}">{{ $article->title }}</a>
                                    </h2>
                                    <div class="content is-size-7">
                                        <p>{{ $article->getCategoryNames() }}</p>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-layouts.post>
