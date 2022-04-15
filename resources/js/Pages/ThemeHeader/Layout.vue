<template>
    <section>
        <form @submit.prevent="onSubmit">
            <header-layout
                v-model="form.layout"
            />

            <hr>
            <header-logo
                v-model="form.logo"
                :logo-url="logoUrl"
            />
        </form>
    </section>
</template>

<script>
    import HeaderLayout from './HeaderLayout';
    import HeaderLogo from './HeaderLogo';
    import { success as successAlert  } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'Layout',

        components: {
            HeaderLayout,
            HeaderLogo,
        },

        props: {
            logoUrl: {
                type: String,
                default: "",
            },
            settings: {
                type: Object,
                required: true,
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
                    layout: parseInt(this.settings.header_layout.value),
                    logo: null
                })
            };
        },

        methods: {
            isFormDirty() {
                return this.form?.isDirty;
            },

            getLayoutForm() {
                return useForm({
                    layout: parseInt(this.settings.header_layout.value),
                    logo: null
                });
            },

            onSubmit() {
                const self = this;
                self.loader = self.$loading.show({});

                self.form.post(
                    route(self.baseRouteName+".layout.update"), {
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                            self.form = self.getLayoutForm();
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