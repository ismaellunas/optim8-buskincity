<template>
    <div class="select" :class="class">
        <select
            v-model="selected"
            :disabled="disabled"
            :name="name"
            @change="$emit('change', $event)"
        >
            <option
                v-if="placeholder"
                :value="null"
            >
                {{ placeholder }}
            </option>

            <slot />
        </select>
    </div>
</template>

<script>
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizSelect',
        props: ['modelValue', 'class', 'disabled', 'placeholder', 'name'],
        emits: ['change', 'update:modelValue'],

        setup(props, { emit }) {
            return {
                selected: useModelWrapper(props, emit),
            };
        },

        methods: {
            focus() {
                this.$refs.input.focus()
            }
        }
    };
</script>
