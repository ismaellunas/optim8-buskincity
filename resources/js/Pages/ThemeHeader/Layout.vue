<template>
    <section>
        <form @submit.prevent="onSubmit">
            <header-layout
                v-model="form.layout"
            />

            <hr>
            <header-logo
                v-model="form.logo"
                :logo-media="logoMedia"
            />
        </form>
    </section>
</template>

<script>
    import HeaderLayout from './HeaderLayout.vue';
    import HeaderLogo from './HeaderLogo.vue';
    import { success as successAlert  } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/vue3';

    export default {
        name: 'ThemeHeaderLayoutTab',

        components: {
            HeaderLayout,
            HeaderLogo,
        },

        props: {
            logoMedia: { type: Object, default: () => {} },
            settings: { type: Object, required: true },
        },

        setup(props) {
            return {
                baseRouteName: usePage().props.baseRouteName ?? null,
                form: useForm({
                    layout: parseInt(props?.settings?.header_layout?.value ?? null),
                    logo: parseInt(props?.settings?.header_logo_media_id?.value ?? null),
                }),
            };
        },

        data() {
            return {
                loader: null,
            };
        },

        methods: {
            isFormDirty() {
                return this.form?.isDirty;
            },

            onSubmit() {
                const self = this;
                self.loader = self.$loading.show({});

                self.form.post(
                    route(self.baseRouteName+".layout.update"), {
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
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