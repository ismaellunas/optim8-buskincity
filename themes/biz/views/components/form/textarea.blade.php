@props([
    'label' => '',
    'type' => 'text',
    'required' => false,
])

<x-field>
    @if ($label)
        <x-label :label="$label" :required="$required" />
    @endif

    <div class="control">
        <textarea class="textarea" {{ $attributes }} @required($required) ></textarea>
    </div>

    {{ $slot }}
</x-field>
