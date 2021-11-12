<template>
    <div>
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
                    v-if="hasImage"
                    class="mb-2"
                    style="width: 200px; border: 1px solid #000"
                    :src="imgUrl !== null ? imgUrl : setting.value"
                />
                <sdb-input-file
                    v-model="formMedia.file"
                    :accept="acceptedTypes"
                    :is-name-displayed="false"
                    @on-file-picked="onFilePicked"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import SdbImage from '@/Sdb/Image';
    import SdbInputFile from '@/Sdb/InputFile';
    import { includes } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'HeaderLogo',

        components: {
            SdbImage,
            SdbInputFile,
        },

        props: {
            modelValue: {
                type: Object,
                required: true,
            },
            setting: {
                type: Object,
                required: true
            },
        },

        setup(props, { emit }) {
            return {
                formMedia: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                acceptedTypes: [
                    '.jpeg',
                    '.jpg',
                    '.png',
                ],
            };
        },

        computed: {
            hasImage() {
                return this.setting.value !== null || this.formMedia.file_url !== null;
            },

            imgUrl() {
                return this.formMedia.file_url ?? null;
            },
        },

        methods: {
            onFilePicked(event) {
                let fileType = this.formMedia.file.type.replace("image/", ".");

                this.formMedia.file_name = "logo";
                this.formMedia.file_url = event.target.result;
                this.formMedia.file_type = fileType.replace(".", "");
                this.formMedia.is_image = includes(this.acceptedTypes, fileType);
            },
        },
    }
</script>