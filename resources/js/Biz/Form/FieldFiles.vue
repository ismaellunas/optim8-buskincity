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
                            :file-label="fileLabel"
                            :accept="acceptedTypes"
                            :disabled="disabled"
                            :is-name-displayed="isNameDisplayed"
                            @on-file-picked="$emit('on-file-picked', $event)"
                        />
                    </div>
                    <div class="column is-narrow">
                        <biz-button-icon
                            title="Remove"
                            type="button"
                            :icon="icon.clear"
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
            title="Add File"
            type="button"
            :icon="icon.add"
            @click="addFileInput"
        />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizInputFile from '@/Biz/InputFile.vue';
    import BizMediaTextItem from '@/Biz/Media/TextItem.vue';
    import { confirmDelete } from '@/Libs/alert';
    import { useModelWrapper } from '@/Libs/utils';
    import { add, clear } from '@/Libs/icon-class';

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
                default:() => []
            },
            mediaComponent: {
                type: String,
                default: 'BizMediaTextItem',
            },
            maxFileNumber: {
                type: Number,
                default: 1,
            },
            selectedFiles: {
                type: Array,
                default: () => [null],
            },
            fileLabel: {
                type: String,
                default: 'Choose a file',
            }
        },

        emits: [
            'on-file-picked',
            'update:modelValue',
            'update:selectedFiles',
        ],

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
                icon: { add, clear },
                files: useModelWrapper(props, emit, 'selectedFiles'),
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
            },

            hasEmptyFileInput() {
                return this.files.includes(null);
            }
        },

        watch: {
            files: {
                handler(files) {
                    this.computedValue.files = files.filter(Boolean);
                },
                deep: true
            },
        },

        created() {
            if (
                this.canAddFileInput
                && this.files.length < 1
                && !this.hasEmptyFileInput
            ) {
                this.addFileInput();
            }
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

                        if (! self.hasEmptyFileInput) {
                            self.addFileInput();
                        }
                    }
                });
            },

            reset() {
                this.files.splice(0);

                if (this.canAddFileInput) {
                    this.addFileInput();
                }
            },
        }
    };
</script>
