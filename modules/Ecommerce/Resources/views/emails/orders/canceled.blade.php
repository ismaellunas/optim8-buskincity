@component('mail::message')
{!! $template !!}

@component('mail::table')
    |               |               |
    | ------------- |:------------- |
    | Product | {{ $line->purchasable->product->translateAttribute('name') }} |
    | SKU | {{ $line->identifier }} |
    | Order Reference | {{ $order->reference }} |
    | User | {{ $order->user->fullName }} |
    | Produdt | {{ $order->reference }} |
    | Booked At | {{ $schedule->formattedBookedAt }} |
    | Duration | {{ $schedule->displayDuration }} |
@endcomponent

{{ __('Regards') }},<br/>
{{ config('app.name') }}

@endcomponent
