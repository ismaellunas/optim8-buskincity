@component('mail::message')
# {{ __('Hello!') }}

{{ __('Please click the button below to verify your email address.') }}

@component('mail::button', ['url' => $url])
{{ __('Verify Email Address') }}
@endcomponent

{{ __('If you did not create an account, no further action is required.') }}

{{ __('Regards') }},<br/>
{{ config('app.name') }}

@slot('subcopy')
{{ __(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => __('Verify Email Address')
    ]
) }} <span class="break-all">[{{ $url }}]({{ $url }})</span>
@endslot
@endcomponent
