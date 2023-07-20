@props([
    'label' => '',
    'type' => 'text',
    'required' => false,
    'isFullwidth' => false,
])

<x-field>
    <x-label :label="$label" :required="$required" />

    <div class="control">
        <div @class(["select", 'is-fullwidth' => $isFullwidth]) @required($required) >
            <select {{ $attributes }}>
                {{ $slot }}
            </select>
        </div>
    </div>

    {{ $notes }}
</x-field>
