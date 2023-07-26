@props([
    'label' => '',
    'type' => 'text',
    'required' => false,
    'isFullwidth' => false,
    'formInputClass' => 'form-input',
])

<x-field>
    <x-label :label="$label" :required="$required" />

    <div class="control">
        <div @class(["select", 'is-fullwidth' => $isFullwidth, $formInputClass]) @required($required) >
            <select {{ $attributes }}>
                {{ $slot }}
            </select>
        </div>
    </div>

    {{ $notes }}
</x-field>
