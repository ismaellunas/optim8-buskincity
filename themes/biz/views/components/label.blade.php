@props([
    'label' => '',
    'required' => false,
])

<label class="label">
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        {{ $label }} @if ($required) <sup class="has-text-danger">*</sup> @endif
    @endif
</label>
