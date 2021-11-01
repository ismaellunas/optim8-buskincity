<template>
    <div class="columns">
        <div class="column">
            <div class="is-pulled-left">
                <b>Upload Logo</b><br>
                Last Saved: {{ lastSaved }}
            </div>
        </div>
    </div>
    <div class="columns">
        <div class="column">
            <sdb-image
                v-if="setting.value !== null"
                :src="setting.value"
                style="width: 200px; border: 1px solid #000"
                class="mb-2"
            ></sdb-image>
            <sdb-input-file
                v-model="file"
                :accept="acceptedTypes"
                :is-name-displayed="false"
                :disabled="isProcessing"
                @on-file-picked="onFilePicked"
            />
        </div>
    </div>
</template>

<script>
    import SdbButton from '@/Sdb/Button';
    import SdbInputFile from '@/Sdb/InputFile';
    import SdbImage from '@/Sdb/Image';
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
            SdbButton,
            SdbInputFile,
            SdbImage,
        },

        props: {
            lastSaved: {type: String, default: '-'},
            setting: {type: Object, default: true},
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
                isUploading: false,
                isDeleting: false,
                formErrors: {},
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
                });
            },
        },
    }
</script>