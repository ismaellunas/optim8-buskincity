<template>
    <div>
        <sdb-label>{{ label }}</sdb-label>

        <sdb-field class="has-addons mb-0">
            <div class="control is-expanded">
                <sdb-input
                    :disabled="disabled"
                    :placeholder="placeholder"
                    :required="required"
                    :value="modelValue"
                    @input="$emit('update:modelValue', $event.target.value)"
                    @keypress="$emit('on-keypress', $event)"
                    @blur="$emit('on-blur', $event)"
                />
            </div>

            <slot name="afterInput"></slot>
        </sdb-field>

        <sdb-input-error :message="message"/>
    </div>
</template>

<script>
    import SdbField from '@/Sdb/Field';
    import SdbInput from '@/Sdb/Input';
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';

    export default {
        name: 'SdbFormInputAddons',
        components: {
            SdbField,
            SdbInput,
            SdbInputError,
            SdbLabel,
        },
        emits: [
            'on-blur',
            'on-keypress',
            'update:modelValue',
        ],
        props: [
            'disabled',
            'label',
            'message',
            'modelValue',
            'placeholder',
            'required',
        ],
    }
</script>
