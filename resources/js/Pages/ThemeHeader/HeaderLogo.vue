<template>
    <div>
        <div class="columns">
            <div class="column">
                <biz-form-media-library
                    v-model="logo"
                    label="Logo"
                    :is-download-enabled="can?.media?.read ?? false"
                    :is-upload-enabled="can?.media?.add ?? false"
                    :medium="logoMedia"
                    :message="error('logo')"
                >
                    <template #note>
                        <p class="help is-info">
                            <ul>
                                <li
                                    v-for="note, index in instructions.mediaLibrary"
                                    :key="index"
                                >
                                    {{ note }}
                                </li>
                            </ul>
                        </p>
                    </template>
                </biz-form-media-library>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormMediaLibrary from '@/Biz/Form/MediaLibrary.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { isEmpty } from 'lodash';

    export default {
        name: 'HeaderLogo',

        components: {
            BizFormMediaLibrary,
        },

        mixins: [
            MixinHasPageErrors
        ],

        inject: [
            'can',
            'instructions',
        ],

        props: {
            logoMedia: { type: Object, default: () => {} },
            modelValue: { type: [String, Number, null], required: true },
        },

        setup(props, { emit }) {
            return {
                logo: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
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