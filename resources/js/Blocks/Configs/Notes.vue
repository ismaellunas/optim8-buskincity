<template>
    <div>
        <label class="label is-size-7">
            {{ label }}
        </label>

        <template
            v-for="(value, index) in computedValue"
            :key="index"
        >
            <biz-form-input-addons
                v-model="computedValue[index]"
                :placeholder="settings?.placeholder"
                :is-small="true"
            >
                <template #afterInput>
                    <div class="control">
                        <biz-button-icon
                            type="button"
                            :icon="removeIcon"
                            class="is-small is-danger component-configurable"
                            :disabled="! canRemoveNote"
                            @click="removeNote(index)"
                        />
                    </div>
                </template>
            </biz-form-input-addons>
        </template>

        <biz-button-icon
            type="button"
            :icon="addIcon"
            class="is-small is-primary"
            @click="addNote()"
        >
            <span>
                Add Note
            </span>
        </biz-button-icon>

        <p
            v-if="settings?.note"
            class="help"
        >
            {{ settings?.note }}
        </p>
    </div>
</template>

<script>
    import BizFormInputAddons from '@/Biz/Form/InputAddons.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { isEmpty } from 'lodash';
    import { add as addIcon, remove as removeIcon } from '@/Libs/icon-class';

    export default {
        name: 'ConfigNotes',

        components: {
            BizFormInputAddons,
            BizButtonIcon,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: Array, required: true },
            settings: { type: Object, default: () => {} },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
                addIcon,
                removeIcon,
            };
        },

        computed: {
            canRemoveNote() {
                return this.computedValue.length > 1;
            },
        },

        mounted() {
            if (isEmpty(this.computedValue)) {
                this.addNote();
            }
        },

        methods: {
            addNote() {
                this.computedValue.push("");
            },

            removeNote(index) {
                if (this.canRemoveNote) {
                    this.computedValue.splice(index, 1);
                }
            },
        },
    }
</script>