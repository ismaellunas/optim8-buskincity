@component('mail::message')
# {{ __('Hello!') }}

{{ __('We\'re writing to let you know that your account is now set up and ready to use. To ensure the security of your account, we recommend that you reset your password.') }}

@component('mail::button', ['url' => $url])
{{ __('Reset Password') }}
@endcomponent

{{ __('This password reset link will expire in :count minutes. So please reset your password as soon as possible.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]) }}

{{ __('Thank you for joining ' . config('app.name') . '.') }}

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
