@props([
    'label' => '',
    'required' => false,
])

<label class="label">
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        {{ $label }} @if ($required) <span class="has-text-danger">*</span> @endif
    @endif
</label>
