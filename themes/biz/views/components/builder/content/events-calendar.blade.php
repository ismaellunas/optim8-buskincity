@if ($isEnabled)
<div @class($uniqueClass)>
    <events-calendar
        :api-key="{{ Illuminate\Support\Js::from($googleApiKey) }}"
        :init-position="{{ Illuminate\Support\Js::from($initPosition) }}"
        :user-city="'{{ $userCity }}'"
        :user-country-code="'{{ $userCountryCode }}'"
        :max-date="'{{ $maxDate }}'"
        :min-date="'{{ $minDate }}'"
        :urls="{{ Illuminate\Support\Js::from($urls) }}"
        :year-range="{{ Illuminate\Support\Js::from($yearRange) }}"
    ></events-calendar>
</div>
@endif
