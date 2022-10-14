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
    | Event Date/Time | {{ $updated['event_date_time'] }} |
    | Envitee Time Zone | {{ $timezone }} |
@endcomponent

{{ __("This event has been updated") }}

<p style="text-decoration: line-through;"><b>Former:</b> <span>{{ $former['event_date_time'] }}</span></p>

<b>{{ __('Updated') }}:</b> <span>{{ $updated['event_date_time'] }}</span>

@if ($message)
<b>{{ __('Message') }}:</b>

@component('mail::panel')
{{ $message }}
@endcomponent
@endif

{{ __('Regards') }},<br/>
{{ config('app.name') }}

@endcomponent
