<template>
    <section>
        <form @submit.prevent="onSubmit">
            <footer-layout
                v-model="form.layout"
            />

            <hr>
            <footer-social-media
                v-model="form.social_media_menus"
            />
        </form>
    </section>
</template>

<script>
    import FooterLayout from './FooterLayout';
    import FooterSocialMedia from './FooterSocialMedia';
    import { success as successAlert  } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'Layout',

        components: {
            FooterLayout,
            FooterSocialMedia,
        },

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
                baseRouteName: usePage().props.value.baseRouteName ?? null,
            };
        },

        data() {
            return {
                loader: null,
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
                self.loader = self.$loading.show({});

                self.form.post(
                    route(self.baseRouteName+".layout.update"), {
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                            this.form = this.getLayoutForm();
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