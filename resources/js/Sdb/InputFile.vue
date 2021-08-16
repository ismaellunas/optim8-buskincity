<template>
    <div class="file" :class="wrapperClass">
        <label class="file-label">
            <input
                class="file-input"
                ref="fileInput"
                type="file"
                :accept="accept.join(', ')"
                @click="resetFile"
                @input="pickFile"
            />
            <span class="file-cta">
                <span class="file-icon">
                    <i class="fas fa-upload"></i>
                </span>
                <span class="file-label" v-if="!hasFile">
                    Choose a file...
                </span>
            </span>

            <span v-if="isNameDisplayed" class="file-name">
                {{ fileName }}
            </span>
        </label>
    </div>
</template>

<script>
    import { isEmpty, concat } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        props: {
            accept: {type: Array, default: []},
            isNameDisplayed: {type: Boolean, default: true},
            modelValue: {},
        },
        setup(props, { emit }) {
            return {
                file: useModelWrapper(props, emit),
            };
        },
        methods: {
            resetFile() {
                this.$refs.fileInput.value = null;
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
        computed: {
            hasFile() {
                return !isEmpty(this.file);
            },
            fileName() {
                if (this.hasFile) {
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
    }
</script>
