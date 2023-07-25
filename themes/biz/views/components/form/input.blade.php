@props([
    'label' => '',
    'type' => 'text',
    'required' => false,
    'formInputClass' => 'form-input'
])

<x-field>
    <x-label :label="$label" :required="$required" />

    <div class="control">
        <input type="{{ $type }}" {{ $attributes->class(['input', $formInputClass]) }} @required($required) />
    </div>

    {{ $slot }}
</x-field>
