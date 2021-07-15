<template>
    <div>
        <form @submit.prevent="submitFile" class="is-clipped">
            <div class="field">
                <div class="control">
                    <sdb-input-file
                        class="is-small"
                        v-model="file"
                        :accept="['image/png', 'image/jpeg']"
                    />
                </div>
            </div>
            <div class="field is-grouped is-centered">
                <div class="control">
                    <sdb-button class="is-link is-small" :disabled="!canUpload">
                        Submit
                    </sdb-button>
                </div>
                <div class="control">
                    <sdb-button
                        type="button"
                        class="is-small"
                        @click="closeForm">
                        Cancel
                    </sdb-button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import NProgress from 'nprogress';
    import SdbButton from '@/Sdb/Button';
    import SdbInputFile from '@/Sdb/InputFile';
    import { useModelWrapper, isBlank } from '@/Libs/utils';

    export default {
        components: {
            SdbInputFile,
            SdbButton,
        },
        props: [
            'entityId',
            'modelValue',
            'uploadRoute',
            'extensions',
        ],
        setup(props, { emit }) {
            return {
                imageSrc: useModelWrapper(props, emit),
            };
        },
        data() {
            return {
                file: null,
            }
        },
        methods: {
            submitFile() {
                let formData = new FormData();
                let self = this;

                formData.append('image', this.file);

                if (!isBlank(this.entityId)) {
                    formData.append('id', this.entityId);
                }

                axios.post(this.uploadRoute, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: event => {
                        NProgress.set(Math.round( (event.loaded * 100) / event.total ));
                    }
                }).then(function(response) {
                    self.imageSrc = response.data.imagePath;
                    self.closeForm();
                })
                .catch(function(error) {
                    console.log(error);
                });
            },
            /*
            onFileChange() {
                this.file = this.$refs.file.files[0];
            },
            */
            resetForm() {
                this.file = null;
            },
            closeForm() {
                this.file = null;
                this.$emit('close-form');
            },
            removeImage() {
                this.imageSrc = null;
            },
        },
        computed: {
            canUpload() {
                return !isBlank(this.file);
            },
        }
    }
</script>
