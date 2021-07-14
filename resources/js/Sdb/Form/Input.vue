<template>
    <div class="field">
        <sdb-label>{{ label }}</sdb-label>

        <div class="control">
            <sdb-input
                :value="modelValue"
                :placeholder="placeholder"
                @input="$emit('update:modelValue', $event.target.value)"
                :disabled="disabled"
            />
        </div>

        <sdb-input-error :message="message"/>
    </div>
</template>

<script>
    import SdbInput from '@/Sdb/Input';
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';

    export default {
        components: {
            SdbInput,
            SdbInputError,
            SdbLabel,
        },
        emits: ['update:modelValue'],
        props: ['label', 'message', 'modelValue', 'placeholder', 'disabled', 'required']
    }
</script>
