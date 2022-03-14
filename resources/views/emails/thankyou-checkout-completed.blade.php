@component('mail::message')
# @lang('Thank You')<br>
##  @lang('For Your Donation')<br>
@lang('We appreciate your generosity.')<br>


@component('mail::table')
| Amount        | Currency        |
| -------------:|:---------------:|
| {{ $amount }} | {{ $currency }} |
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
