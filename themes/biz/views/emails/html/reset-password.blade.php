@component('mail::message')
# {{ __('Hello!') }}

{{ __('You are receiving this email because we received a password reset request for your account.') }}

@component('mail::button', ['url' => $url])
{{ __('Reset Password') }}
@endcomponent

{{ __('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]) }}

{{ __('If you did not request a password reset, no further action is required.') }}

{{ __('Regards') }},<br/>
{{ config('app.name') }}

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
