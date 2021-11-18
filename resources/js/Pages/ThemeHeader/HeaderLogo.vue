<template>
    <div>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>Upload Logo</b><br>
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <sdb-image
                    v-if="hasImage"
                    class="mb-2"
                    style="width: 200px; border: 1px solid #000"
                    :src="imgUrl"
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
    import { useModelWrapper } from '@/Libs/utils';
    import { isEmpty } from 'lodash';

    export default {
        name: 'HeaderLogo',

        components: {
            SdbImage,
            SdbInputFile,
        },

        props: {
            logoUrl: {
                type: String,
                default: "",
            },
            modelValue: {
                type: Object,
                required: true,
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
                return !isEmpty(this.logoUrl) || this.formMedia.file_url !== null;
            },

            imgUrl() {
                return this.formMedia.file_url ?? this.logoUrl;
            },
        },

        methods: {
            onFilePicked(event) {
                this.formMedia.file_url = event.target.result;
            },
        },
    }
</script>