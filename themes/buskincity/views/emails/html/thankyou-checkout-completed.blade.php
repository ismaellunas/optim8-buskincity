@component('mail::message')
# {{ __('Thank You') }}<br>
##  {{ __('For Your Donation') }}<br>

{{ __('We appreciate your generosity.') }}<br>

@component('mail::table')
| Currency        | Amount        |
|:---------------:| -------------:|
| {{ $currency }} | {{ $amount }} |
@endcomponent

{{ __('Thanks')}},<br>
{{ config('app.name') }}
@endcomponent
