<template>
    <div class="select" :class="class" >
        <select v-model="selected" @change="$emit('change')">
            <slot></slot>
        </select>
    </div>
</template>

<script>
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        props: ['modelValue', 'class'],
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
    }
</script>
