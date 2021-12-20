<tr>
    <td>
        <article class="media">
            @if ($hasCover)
                <x-image
                    class="media-left"
                    ratio="is-64x64"
                    :src="$post->thumbnail_url"
                />
            @else
                <div class="media-left" style="width: 64px;"></div>
            @endif

            <div class="media-content">
                <div class="content">
                    <p>
                        <span @class(['mr-2'=> $hasCategory])>
                            {{ $firstCategoryName }}
                        </span>

                        <x-tag class="is-info">
                            {{ strtoupper($post->locale) }}
                        </x-tag>

                        <br>

                        <a href="{{ $link }}">
                            <strong>{{ $post->title }}</strong>
                        </a>

                        <br>

                        <span>{{ $post->excerpt ?? '' }}</span>
                    </p>
                </div>
            </div>

            <div class="media-right">
                {{ $actions ?? null }}
            </div>
        </article>
    </td>
</tr>