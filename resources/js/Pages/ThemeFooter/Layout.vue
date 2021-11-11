<template>
    <section>
        <form @submit.prevent="onSubmit">
            <footer-layout
                v-model="form.layout"
                :setting="settings.footer_layout"
            />
        </form>
    </section>
</template>

<script>
    import FooterLayout from './FooterLayout';
    import { success as successAlert  } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'Layout',

        components: {
            FooterLayout,
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
                    layout: parseInt(this.settings.footer_layout.value),
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