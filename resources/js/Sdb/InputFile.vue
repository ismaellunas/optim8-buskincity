<template>
    <div class="file has-name">
        <label class="file-label">
            <input
                :accept="accept.join(', ')"
                @change="onFileChange"
                class="file-input"
                type="file"
                />
            <span class="file-cta">
                <span class="file-icon">
                    <i class="fas fa-upload"></i>
                </span>
                <span class="file-label" v-if="!file">
                    Choose a file...
                </span>
            </span>
            <span class="file-name">
                {{ fileName }}
            </span>
        </label>
    </div>
</template>

<script>
    import { useModelWrapper, isBlank } from '@/Libs/utils';

    export default {
        props: {
            modelValue: {},
            accept: Array,
        },
        setup(props, { emit }) {
            return {
                file: useModelWrapper(props, emit),
            };
        },
        methods: {
            onFileChange(event) {
                let files = event.target.files || event.dataTransfer.files;
                if (!files.length) {
                    return;
                }
                this.file = files[0];
            }
        },
        computed: {
            fileName() {
                if (!isBlank(this.file)) {
                    return this.file.name
                }
                return "...";
            },
        },
    }
</script>
