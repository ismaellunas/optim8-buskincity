<template>
    <div class="field">
        <sdb-label>{{ label }}</sdb-label>

        <div class="control">
            <sdb-textarea
                :disabled="disabled"
                :placeholder="placeholder"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
            />
        </div>

        <sdb-input-error :message="message"/>
    </div>
</template>

<script>
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';
    import SdbTextarea from '@/Sdb/Textarea';

    export default {
        components: {
            SdbInputError,
            SdbLabel,
            SdbTextarea,
        },
        emits: ['update:modelValue'],
        props: ['label', 'message', 'modelValue', 'placeholder', 'disabled']
    }
</script>
