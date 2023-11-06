<template>
    <form @submit.prevent="onSubmit">
        <layout-header
            v-model="form.layout"
        />

        <hr>

        <div class="columns">
            <div class="column">
                <biz-form-media-library
                    v-model="form.logo"
                    :label="i18n.logo"
                    :placeholder="i18n.open_media_library"
                    :is-browse-enabled="can?.media?.browse ?? false"
                    :is-download-enabled="can?.media?.read ?? false"
                    :is-edit-enabled="can?.media?.edit ?? false"
                    :is-upload-enabled="can?.media?.add ?? false"
                    :medium="logoMedia"
                    :dimension="dimensions.logo"
                    :message="error('logo', 'layout')"
                    :instructions="instructions.mediaLibrary"
                />
            </div>
        </div>

        <div class="field is-grouped is-grouped-right">
            <div class="control">
                <biz-button class="is-primary">
                    <span>
                        {{ i18n.save }}
                    </span>
                </biz-button>
            </div>
        </div>
    </form>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizFormMediaLibrary from '@/Biz/Form/MediaLibrary.vue';
    import LayoutHeader from './LayoutHeader.vue';
    import { success as successAlert  } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';

    export default {
        name: 'ThemeHeaderLayout',

        components: {
            BizButton,
            LayoutHeader,
            BizFormMediaLibrary,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        inject: {
            can: {},
            dimensions: {},
            instructions: {},
        },

        props: {
            logoMedia: { type: Object, default: () => {} },
            settings: { type: Object, required: true },
        },

        setup(props) {
            const settings = computed(() => props.settings);

            return {
                baseRouteName: usePage().props.baseRouteName ?? null,
                form: useForm({
                    layout: parseInt(settings.value.header_layout?.value) ?? null,
                    logo: parseInt(settings.value.header_logo_media_id?.value) ?? null,
                }),
                i18n: computed(() => usePage().props.i18n),
            };
        },

        methods: {
            isFormDirty() {
                return this.form?.isDirty;
            },

            onSubmit() {
                const self = this;

                self.onStartLoadingOverlay();

                self.form.post(
                    route(self.baseRouteName+".layout.update"), {
                        errorBag: 'layout',
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                        },
                        onFinish: () => {
                            self.onEndLoadingOverlay();
                        },
                    }
                );
            },
        },
    }
</script>