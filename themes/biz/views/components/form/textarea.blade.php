@props([
    'label' => '',
    'type' => 'text',
    'required' => false,
])

<x-field>
    <x-label :label="$label" :required="$required" />

    <div class="control">
        <textarea class="textarea" {{ $attributes }} @required($required) ></textarea>
    </div>

    {{ $slot }}
</x-field>
