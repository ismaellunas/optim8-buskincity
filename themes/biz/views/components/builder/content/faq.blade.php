<{{ $headingTag }} @class($headingClasses)>{!! $headingContent !!}</{{ $headingTag }}>

<div class="column p-0">
    @foreach($faqContents as $content)
        <article class="media">
            <div class="media-left">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="media-content">
                <div class="content has-text-weight-bold">
                    <p>{{ $content['question'] }}</p>
                </div>

                <article class="media">
                    <div class="content">
                        {!! $content['answer'] !!}
                    </div>
                </article>
            </div>
        </article>
    @endforeach
</div>