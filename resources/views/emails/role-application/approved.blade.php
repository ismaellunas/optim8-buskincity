@component('mail::message')
# {{ __('Application approved') }}

{{ __('Hello :name,', ['name' => $user->first_name]) }}

{{ __('Your application to become a :role has been approved.', ['role' => $roleLabel]) }}

{{ __('You can sign in to the admin panel using the email address and password you chose when you submitted your application.') }}

@component('mail::button', ['url' => $loginUrl])
{{ __('Sign in to admin') }}
@endcomponent

{{ __('If you did not submit this application, please contact us immediately.') }}

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
@endcomponent
