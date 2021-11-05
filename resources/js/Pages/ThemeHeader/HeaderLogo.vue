<template>
    <div>
        <sdb-error-notifications :errors="formErrors"/>

        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>Upload Logo</b><br>
                    Last Saved: {{ setting.updated_at }}
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <sdb-image
                    v-if="setting.value !== null"
                    class="mb-2"
                    style="width: 200px; border: 1px solid #000"
                    :src="setting.value"
                />
                <sdb-input-file
                    v-model="file"
                    :accept="acceptedTypes"
                    :disabled="isProcessing"
                    :is-name-displayed="false"
                    @on-file-picked="onFilePicked"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';;
    import SdbImage from '@/Sdb/Image';
    import SdbInputFile from '@/Sdb/InputFile';
    import { success as successAlert  } from '@/Libs/alert';
    import { usePage, useForm } from '@inertiajs/inertia-vue3';
    import { includes } from 'lodash';

    function getEmptyFormMedia() {
        return {
            file: null,
            file_url: null,
            file_name: null,
        };
    };

    export default {
        name: 'HeaderLogo',

        components: {
            SdbErrorNotifications,
            SdbImage,
            SdbInputFile,
        },

        props: {
            setting: {
                type: Object,
                required: true
            },
        },

        setup() {
            return {
                baseRouteName: usePage().props.value.baseRouteName ?? null,
            };
        },

        data() {
            return {
                formMedia: getEmptyFormMedia(),
                file: null,
                acceptedTypes: [
                    '.jpeg',
                    '.jpg',
                    '.png',
                ],
                formErrors: {},
                isDeleting: false,
                isUploading: false,
                loader: null,
            };
        },

        computed: {
            isProcessing() {
                return this.isUploading || this.isDeleting;
            },
        },

        methods: {
            onFilePicked(event) {
                let fileType = this.file.type.replace("image/", ".");

                this.formMedia = {
                    file: this.file,
                    file_name: 'logo',
                    file_url: event.target.result,
                    file_type: fileType.replace(".", ""),
                    is_image: includes(this.acceptedTypes, fileType),
                };

                this.submit();
            },

            submit() {
                const currentForm = this.formMedia;
                const self = this;
                let url = null;

                self.loader = self.$loading.show({});

                const form = useForm(currentForm);
                form.post(
                    route(this.baseRouteName+".logo.update"), {
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                            self.formErrors = {};
                        },
                        onError: errors => {
                            self.formErrors = errors;
                        },
                        onFinish: () => {
                            self.loader.hide();
                        },
                    }
                );
            },
        },
    }
</script>