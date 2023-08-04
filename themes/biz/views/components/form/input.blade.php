@props([
    'label' => '',
    'type' => 'text',
    'required' => false,
    'formInputClass' => 'form-input'
])

<x-field>
    @if ($label)
        <x-label :label="$label" :required="$required" />
    @endif

    <div class="control">
        <input type="{{ $type }}" {{ $attributes->class(['input', $formInputClass]) }} @required($required) />
    </div>

    {{ $slot }}
</x-field>
