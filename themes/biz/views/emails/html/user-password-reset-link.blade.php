@component('mail::message')
{!! $body !!}

@slot('subcopy')
{{ __(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => __('Reset Password'),
    ]
) }} <span class="break-all">[{{ $url }}]({{ $url }})</span>
@endslot
@endcomponent
