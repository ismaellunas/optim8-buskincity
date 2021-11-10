<template>
    <section>
        <form @submit.prevent="onSubmit">
            <header-layout
                v-model="form.layout"
                :setting="settings.header_layout"
            />

            <hr>
            <header-logo
                v-model="form.logo"
                :setting="settings.header_logo_url"
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
                    logo: {
                        file: null,
                        file_name: null,
                        file_url: null,
                        file_type: null,
                        is_image: null,
                    }
                })
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