<template>
    <div class="select">
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

        props: {
            disabled: { type: Boolean, default: false },
            modelValue: { type: [String, Number, Boolean, null], required: true },
            name: { type: [String, null], default: null },
            placeholder: { type: [String, null], default: null },
        },

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
