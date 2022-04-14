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
                <biz-image
                    v-if="hasImage"
                    class="mb-2"
                    style="width: 200px; border: 1px solid #000"
                    :src="logoImgUrl"
                />
                <biz-input-file
                    v-model="formMedia"
                    :accept="acceptedTypes"
                    :is-name-displayed="false"
                    @on-file-picked="onFilePicked"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import BizImage from '@/Biz/Image';
    import BizInputFile from '@/Biz/InputFile';
    import { useModelWrapper } from '@/Libs/utils';
    import { isEmpty } from 'lodash';

    export default {
        name: 'HeaderLogo',

        components: {
            BizImage,
            BizInputFile,
        },

        props: {
            logoUrl: {
                type: String,
                default: "",
            },
            modelValue: {
                type: [File, Blob, null],
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
                logoImgUrl: this.logoUrl,
            };
        },

        computed: {
            hasImage() {
                return !isEmpty(this.logoImgUrl);
            },
        },

        methods: {
            onFilePicked(event) {
                this.logoImgUrl = event.target.result;
            },
        },
    }
</script>