<template>
    <div
        class="file"
        :class="wrapperClass"
    >
        <label class="file-label">
            <input
                ref="fileInput"
                class="file-input"
                type="file"
                :accept="accept.join(', ')"
                :disabled="disabled || isDelayed"
                @click="clickDelay"
                @input="pickFile"
            >
            <span class="file-cta">
                <span class="file-icon">
                    <i class="fas fa-upload" />
                </span>
                <span
                    v-if="!hasFile"
                    class="file-label"
                >
                    {{ fileLabel }}
                </span>
            </span>

            <span
                v-if="isNameDisplayed"
                class="file-name"
            >
                {{ fileName }}
            </span>
        </label>
    </div>
</template>

<script>
    import { concat } from 'lodash';
    import { isBlank, useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizInputFile',

        props: {
            accept: {
                type: Array,
                default: () => []
            },
            isNameDisplayed: {
                type: Boolean,
                default: true
            },
            modelValue: {
                type: [File, Blob, null],
                required: true,
            },
            disabled: {
                type: Boolean,
                default: false,
            },
            fileLabel: {
                type: String,
                default: 'Choose a file'
            },
            displayedFileName: {
                type: String,
                default: null
            }
        },

        emits: [
            'on-file-picked',
            'update:modelValue'
        ],

        setup(props, { emit }) {
            return {
                file: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                isDelayed: false
            }
        },

        computed: {
            hasFile() {
                return !isBlank(this.file);
            },

            fileName() {
                if (this.displayedFileName) {
                    return this.displayedFileName;
                } else if (this.hasFile) {
                    return this.file.name
                }
                return "...";
            },

            wrapperClass() {
                return concat(
                    this.isNameDisplayed ? 'has-name': null,
                ).filter(Boolean);
            },
        },

        methods: {
            resetFile() {
                this.$refs.fileInput.value = null;
            },

            disabledFewSeconds(){
                setTimeout(() => {
                    this.isDelayed = true;
                }, 1);
                setTimeout(() => {
                    this.isDelayed = false;
                }, 2000)
            },

            clickDelay() {
                this.resetFile();
                this.disabledFewSeconds();
            },

            pickFile() {
                let input = this.$refs.fileInput
                let file = input.files

                if (file && file[0]) {
                    let reader = new FileReader
                    reader.onload = (event) => {
                        this.$emit('on-file-picked', event);
                    }
                    reader.readAsDataURL(file[0])
                    this.file = file[0];
                }
            },
        },
    };
</script>
