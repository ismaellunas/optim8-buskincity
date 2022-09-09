@component('mail::message')

@component('mail::table')
    |               |               |
    | ------------- |:------------- |
    | Product | {{ $line->purchasable->product->translateAttribute('name') }} |
    | SKU | {{ $line->identifier }} |
    | Order Reference | {{ $order->reference }} |
    | User | {{ $order->user->fullName }} |
    | Duration | {{ $rescheduledEvent->displayDuration }} |
@endcomponent

{{ __("This event has been updated") }}

@component('mail::table')
    | From            | To             |
    | :-------------: |:-------------: |
    | <span style="text-decoration: line-through;">{{ $rescheduledEvent->formattedBookedAt }}</span> | {{ $upcomingEvent->formattedBookedAt }} |
@endcomponent

{{ __('Regards') }},<br/>
{{ config('app.name') }}

@endcomponent
