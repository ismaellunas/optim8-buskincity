@props([
    'label' => '',
    'type' => 'text',
    'required' => false,
    'isFullwidth' => false,
    'formInputClass' => 'form-input',
])

<x-field>
    @if ($label)
        <x-label :label="$label" :required="$required" />
    @endif

    <div class="control">
        <div @class(["select", 'is-fullwidth' => $isFullwidth, $formInputClass]) @required($required) >
            <select {{ $attributes }}>
                {{ $slot }}
            </select>
        </div>
    </div>

    {{ $notes }}
</x-field>
