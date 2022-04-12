<x-layouts.exception>
    <h1
        class="has-text-white has-text-weight-bold"
        style="font-size: 10em"
    >
        {{ $statusCode }}
    </h1>
    <p class="is-size-1 has-text-weight-bold has-text-grey-lighter">
        {{ __('Opss! Something Error') }}
    </p>
    <p class="is-size-5">{{ $message }}</pclass=>
</x-layouts.exception>