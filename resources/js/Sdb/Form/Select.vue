<template>
    <div class="field">
        <sdb-label>{{ label }}</sdb-label>

        <div class="control">
            <sdb-select
                :disabled="disabled"
                :placeholder="placeholder"
                :class="class"
                v-model="selected"
            >
                <slot></slot>
            </sdb-select>
        </div>

        <sdb-input-error :message="message"/>
    </div>
</template>

<script>
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';
    import SdbSelect from '@/Sdb/Select';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            SdbInputError,
            SdbLabel,
            SdbSelect,
        },
        props: ['label', 'message', 'modelValue', 'placeholder', 'disabled', 'class'],
        setup(props, { emit }) {
            return {
                selected: useModelWrapper(props, emit),
            };
        },
    }
</script>
