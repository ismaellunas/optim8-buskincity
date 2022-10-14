@component('mail::message')
{{ __('Hi :name,', ['name' => $toName]) }}

{{ __('The event below has been canceled.') }}

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

@if ($message)
<b>{{ __('Message') }}:</b>

@component('mail::panel')
{{ $message }}
@endcomponent
@endif

{!! $template !!}

{{ __('Regards') }},<br/>
{{ config('app.name') }}

@endcomponent
