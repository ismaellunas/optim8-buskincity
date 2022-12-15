<div @class($uniqueClass)>
    <event-calendar
        :api-key="{{ Illuminate\Support\Js::from($googleApiKey) }}"
        :init-position="{{ Illuminate\Support\Js::from($initPosition) }}"
    ></event-calendar>
</div>
