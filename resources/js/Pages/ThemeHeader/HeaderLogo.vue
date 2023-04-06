<template>
    <div>
        <div class="columns">
            <div class="column">
                <biz-form-media-library
                    v-model="logo"
                    :label="i18n.logo"
                    :placeholder="i18n.open_media_library"
                    :is-download-enabled="can?.media?.read ?? false"
                    :is-upload-enabled="can?.media?.add ?? false"
                    :medium="logoMedia"
                    :message="error('logo')"
                    :instructions="instructions.mediaLibrary"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormMediaLibrary from '@/Biz/Form/MediaLibrary.vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'HeaderLogo',

        components: {
            BizFormMediaLibrary,
        },

        mixins: [
            MixinHasPageErrors
        ],

        inject: {
            can: {},
            instructions: {},
            i18n: { default: () => ({
                logo : 'Logo',
                open_media_library : 'Open Media Library',
            }) },
        },

        props: {
            logoMedia: { type: Object, default: () => {} },
            modelValue: { type: [String, Number, null], required: true },
        },

        setup(props, { emit }) {
            return {
                logo: useModelWrapper(props, emit),
            };
        },
    }
</script>