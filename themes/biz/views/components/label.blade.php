@props([
    'label' => '',
    'required' => false,
])

<label class="label">{{ $label.($required ? " *" : "") }}</label>
