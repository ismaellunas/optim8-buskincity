<template>
    <div>
        <form @submit.prevent="submitFile" class="is-clipped">
            <div class="field">
                <div class="control">
                    <input
                        type="file"
                        id="file"
                        ref="file"
                        @change="onFileChange()"
                        />
                </div>
            </div>
            <div class="field is-grouped is-pulled-right">
                <div class="control">
                    <button class="button is-link is-small">Submit</button>
                </div>
                <div class="control">
                    <button
                        type="button"
                        class="button is-small"
                        @click="closeForm">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import NProgress from 'nprogress';
    import { useModelWrapper, isBlank } from '@/Libs/utils';

    export default {
        props: ['uploadRoute', 'modelValue'],
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
            submitFile(){
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
                .catch(function() {
                });
            },
            onFileChange() {
                this.file = this.$refs.file.files[0];
            },
            closeForm() {
                this.$emit('close-form');
            }
        },
        computed: {
            canUpload() {
                return isBlank(this.file);
            },
        }
    }
</script>
