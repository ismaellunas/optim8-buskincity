<template>
    <section>
        <form @submit.prevent="onSubmit">
            <footer-layout
                v-model="form.layout"
                :setting="settings.footer_layout"
            />

            <hr>
            <footer-link
                v-model="form.links"
            />
        </form>
    </section>
</template>

<script>
    import FooterLayout from './FooterLayout';
    import FooterLink from './FooterLink';
    import { success as successAlert  } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'Layout',

        components: {
            FooterLayout,
            FooterLink,
        },

        props: {
            settings: {
                type: Object,
                required: true,
            },
            links: {
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
                    links: cloneDeep(this.links),
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