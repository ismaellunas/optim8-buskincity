@props([
    'label' => '',
    'type' => 'text',
    'required' => false,
])

<x-field>
    <x-label :label="$label" :required="$required" />

    <div class="control">
        <input class="input" type="{{ $type }}" {{ $attributes }} @required($required) />
    </div>

    {{ $slot }}
</x-field>
