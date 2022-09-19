@component('mail::message')
{{ __('Hi :name,', ['name' => $toName]) }}

{{ __('Today’s the day! This is a reminder that :productName is set to begin today at :startedTime.',
    ['productName' => $productName, 'startedTime' => $startedTime]) }}

{{ __('Here’s a quick overview the event:') }}

@component('mail::table')
    |               |               |
    | ------------- |:------------- |
    | Event | {{ $productName }} |
    | Duration | {{ $duration }} |
    | Event Date/Time | {{ $eventDateTime }} |
    | Envitee Time Zone | {{ $timezone }} |
@endcomponent

{{ __('Regards') }},<br/>
{{ config('app.name') }}
@endcomponent
