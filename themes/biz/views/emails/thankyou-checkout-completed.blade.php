@component('mail::message')
# @lang('Thank You')<br>
##  @lang('For Your Donation')<br>
@lang('We appreciate your generosity.')<br>


@component('mail::table')
| Currency        | Amount        |
|:---------------:| -------------:|
| {{ $currency }} | {{ $amount }} |
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
