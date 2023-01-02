@component('mail::message')
# {{ __('Hello Admin,') }}
## {{ __('There is ' . $totalError . ' errors found today on ' . config('app.name')) }}

Here is the details:
@component('mail::table')
| #    | URL        | File        | Line        | Total-Hit      | Message        |
|:----:|:-----------|:------------|:-----------:|:--------------:|:---------------|
@foreach ($errorLogs as $log)
| {{ $loop->iteration }} | {{ $log['url'] }} | {{ $log['file'] }} | {{ $log['line'] }} | {{ $log['total_hit'] }} | {{ $log['message'] }} |
@endforeach
@endcomponent

{{ __('Thanks')}},<br>
{{ config('app.name') }}
@endcomponent

<style>
    .inner-body {
        width: 90% !important;
    }

    table > thead > tr > th:nth-child(3) {
        width: 25% !important;
    }

    table > thead > tr > th:nth-child(6) {
        width: 30% !important;
    }
</style>