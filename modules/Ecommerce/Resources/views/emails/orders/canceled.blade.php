@component('mail::message')
{!! $template !!}

@component('mail::table')
    |               |               |
    | ------------- |:------------- |
    | Product | {{ $line->purchasable->product->translateAttribute('name') }} |
    | SKU | {{ $line->identifier }} |
    | Order Reference | {{ $order->reference }} |
    | User | {{ $order->user->fullName }} |
    | Product | {{ $order->reference }} |
    | Booked At | {{ $event->formattedBookedAt }} |
    | Duration | {{ $event->displayDuration }} |
@endcomponent

{{ __('Regards') }},<br/>
{{ config('app.name') }}

@endcomponent
