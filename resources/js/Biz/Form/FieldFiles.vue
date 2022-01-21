<template>
    <biz-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <div
            v-for="medium in listMedia"
            :key="medium.id"
            class="columns mb-0"
        >
            <div class="column is-full">
                <component
                    :is="mediaComponent"
                    :medium="medium"
                    @on-delete-clicked="deleteMedium($event)"
                />
            </div>
        </div>

        <div
            v-for="file, index in files"
            :key="index"
            class="mb-3"
        >
            <div class="control">
                <div class="columns">
                    <div class="column is-narrow">
                        <biz-input-file
                            v-model="files[ index ]"
                            v-bind="$attrs"
                            :accept="acceptedTypes"
                            :disabled="disabled"
                            :is-name-displayed="isNameDisplayed"
                            @on-file-picked="$emit('on-file-picked', $event)"
                        />
                    </div>
                    <div class="column is-narrow">
                        <biz-button-icon
                            icon="fas fa-times"
                            title="Remove"
                            type="button"
                            @click="removeFileInput(index)"
                        />
                    </div>
                </div>
            </div>

            <slot name="note" />

            <div>
                <biz-input-error :message="fileMessages[ index ]" />
            </div>
        </div>

        <biz-button-icon
            v-if="canAddFileInput"
            icon="fas fa-plus"
            title="Add File"
            type="button"
            @click="addFileInput"
        />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizFormField from '@/Biz/Form/Field';
    import BizInputError from '@/Biz/InputError';
    import BizInputFile from '@/Biz/InputFile';
    import BizMediaTextItem from '@/Biz/Media/TextItem';
    import { confirmDelete } from '@/Libs/alert';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormFieldFiles',

        components: {
            BizButtonIcon,
            BizFormField,
            BizInputFile,
            BizInputError,
            BizMediaTextItem,
        },

        inheritAttrs: false,

        props: {
            acceptedTypes: {
                type: Array,
                default:() => [],
            },
            isNameDisplayed: {
                type: Boolean,
                default: true,
            },
            label: {
                type: String,
                default: null,
            },
            fileMessages: {
                type: Array,
                default: () => [],
            },
            message: {
                type: Object,
                default: () => {},
            },
            modelValue: {
                type: [Object, null],
                required: true,
            },
            disabled: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false
            },
            media: {
                type: Array,
                default: null
            },
            mediaComponent: {
                type: String,
                default: 'BizMediaTextItem',
            },
            maxFileNumber: {
                type: Number,
                default: 1,
            }
        },

        emits: [
            'on-file-picked',
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                files: [null],
            };
        },

        computed: {
            hasMedia() {
                return this.media && this.media.length > 0;
            },

            numberLeft() {
                let mediaLength = 0;

                if (this.hasMedia) {
                    mediaLength = this.media.length;
                }

                return this.maxFileNumber - mediaLength;
            },

            listMedia() {
                const self = this;

                return this.media.filter((medium) => {
                    return !self
                        .computedValue
                        .delete_media
                        .includes(medium.id);
                });
            },

            canAddFileInput() {

                return (this.files.length + this.listMedia.length) < this.maxFileNumber;
            }
        },

        watch: {
            files: {
                handler(val, oldVal) {
                    this.computedValue.files = this.files.filter(Boolean);
                },
                deep: true
            },
        },

        methods: {
            addFileInput() {
                if (this.canAddFileInput) {
                    this.files.push(null);
                }
            },

            removeFileInput(index) {
                this.files.splice(index, 1);
            },

            deleteMedium(medium) {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.computedValue.delete_media.push(medium.id);
                    }
                });
            }
        }
    };
</script>
