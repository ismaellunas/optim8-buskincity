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
                :logo="logo"
                :setting="settings.header_logo_media_id"
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
            logo: {
                type: Object,
                default() {
                    return {};
                },
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
                    logo: {
                        file: null,
                        file_url: null,
                        media_id: this.settings.header_logo_media_id.value,
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