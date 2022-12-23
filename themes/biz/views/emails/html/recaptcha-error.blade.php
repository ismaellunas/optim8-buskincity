@component('mail::message')
# {{ __('Hello Admin,') }}
## {{ __('We have a little problem') }}

{{ $message }}<br>

{{ __('Thanks')}},<br>
{{ config('app.name') }}
@endcomponent
