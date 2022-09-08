@component('mail::message')
{{ __('Hi :name,', ['name' => $toName]) }}

{{ __('A new event has been scheduled.') }}

@component('mail::table')
    |               |               |
    | ------------- |:------------- |
    | Event | {{ $productName }} |
    | Reference | {{ $order->reference }} |
    | Invitee | {{ $inviteeName }} |
    | Invitee Email | {{ $inviteeEmail }} |
    | Duration | {{ $duration }} |
    | Event Date/Time | {{ $eventDateTime }} |
    | Envitee Time Zone | {{ $timezone }} |
@endcomponent

{!! $template !!}

{{ __('Regards') }},<br/>
{{ config('app.name') }}

@endcomponent
