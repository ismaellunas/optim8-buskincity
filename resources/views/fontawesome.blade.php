@inject('settingService', 'App\Services\SettingService')
@php
    $kitName = $settingService->getFontawesomeKitName();
@endphp

@env('production')
    @if (!empty($kitName))
        <script src="https://kit.fontawesome.com/{{ $kitName }}.js" crossorigin="anonymous"></script>
    @else
        @vite(['resources/sass/fontawesome-local.sass'])
    @endif
@endenv

@env('local')
    @if (config('constants.fontawesome_local') || empty($kitName))
        @vite(['resources/sass/fontawesome-local.sass'])
    @else
        <script src="https://kit.fontawesome.com/{{ $kitName }}.js" crossorigin="anonymous"></script>
    @endif
@endenv
