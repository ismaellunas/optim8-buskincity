<template>
    <div>
        <label class="label is-size-7">{{ label }}</label>

        <template
            v-for="(option, index) in computedValue"
            :key="index"
        >
            <div class="field is-horizontal">
                <div class="field-body">
                    <form-input
                        v-model="option.id"
                        class="is-small"
                        placeholder="id"
                    />
                    <form-input
                        v-model="option.value"
                        class="is-small"
                        placeholder="value"
                    />
                    <div
                        v-if="canRemove"
                        class="field"
                    >
                        <p class="control">
                            <button
                                type="button"
                                class="button is-small is-danger component-configurable"
                                @click="onDelete(index)"
                            >
                                <i :class="icon.remove" />
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <button
            type="button"
            class="button is-success is-small"
            @click="onAdd"
        >
            <i :class="icon.add" />
        </button>
    </div>
</template>

<script>
    import FormInput from '@/Biz/Form/Input.vue';
    import icon from '@/Libs/icon-class';
    import { useModelWrapper } from '@/Libs/utils';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'AddOption',

        components: {
            FormInput,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: Array, default: () => [] },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                icon,
                emptyOption: {
                    id: null,
                    value: null,
                },
            };
        },

        computed: {
            canRemove() {
                return this.computedValue.length > 1;
            },
        },

        methods: {
            onAdd() {
                this.computedValue.push(
                    cloneDeep(this.emptyOption)
                );
            },

            onDelete(index) {
                this.computedValue.splice(index, 1);
            },
        },
    }
</script>