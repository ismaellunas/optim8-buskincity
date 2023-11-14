<template>
    <form @submit.prevent="onSubmit">
        <footer-layout
            v-model="form.layout"
        />

        <hr>

        <footer-social-media
            v-model="form.social_media_menus"
        />

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
    import MixinHasTranslation from '@/Mixins/HasTranslation';
    import BizButton from '@/Biz/Button.vue';
    import FooterLayout from './FooterLayout.vue';
    import FooterSocialMedia from './FooterSocialMedia.vue';
    import { success as successAlert  } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/vue3';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'ThemeFooterLayoutTab',

        components: {
            BizButton,
            FooterLayout,
            FooterSocialMedia,
        },

        mixins: [
            MixinHasLoader,
            MixinHasTranslation,
        ],

        props: {
            settings: {
                type: Object,
                required: true,
            },
            socialMediaMenus: {
                type: Array,
                default:() => [],
            },
        },

        setup() {
            return {
                baseRouteName: usePage().props.baseRouteName ?? null,
            };
        },

        data() {
            return {
                form: useForm({
                    layout: parseInt(this.settings.footer_layout.value),
                    social_media_menus: cloneDeep(this.socialMediaMenus),
                }),
            };
        },

        mounted() {
            this.form = this.getLayoutForm();
        },

        methods: {
            isFormDirty() {
                return this.form?.isDirty;
            },

            getLayoutForm() {
                return useForm({
                    layout: parseInt(this.settings.footer_layout.value),
                    social_media_menus: cloneDeep(this.socialMediaMenus),
                });
            },

            onSubmit() {
                const self = this;

                self.onStartLoadingOverlay();

                self.form.post(
                    route(self.baseRouteName+".layout.update"), {
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);

                            this.form = this.getLayoutForm();
                        },
                        onFinish: () => self.onEndLoadingOverlay(),
                    }
                );
            },
        },
    }
</script>