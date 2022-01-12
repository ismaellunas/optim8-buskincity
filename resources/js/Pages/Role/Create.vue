<template>
    <app-layout>
        <template #header>{{ title }}</template>

        <biz-error-notifications :errors="$page.props.errors"/>

        <div class="mb-6">
            <form
                class="columns"
                method="post"
                @submit.prevent="onSubmit"
            >
                <div class="column">
                    <fieldset
                        class="box"
                        :disabled="isProcessing"
                    >
                        <form-role
                            v-model="form"
                            :errors="errors"
                            :permission-options="permissions"
                        ></form-role>

                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button-link
                                    :href="route(baseRouteName+'.index')"
                                    class="is-link is-light">
                                    Cancel
                                </biz-button-link>
                            </div>
                            <div class="control">
                                <biz-button class="is-link">
                                    Create
                                </biz-button>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import FormRole from '@/Pages/Role/Form';
    import { map } from 'lodash';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            FormRole,
        },
        props: {
            baseRouteName: String,
            errors: Object,
            permissions: {},
            title: String,
        },
        setup(props) {
            const role = props.record;
            const form = {
                name: null,
                permissions: [],
            };

            return {
                form: useForm(form),
            };
        },
        data() {
            return {
                isProcessing: false,
                loader: null,
            };
        },
        methods: {
            onSubmit() {
                const self = this;
                const form = self.form;
                form.post(route(this.baseRouteName+'.store'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        form.isDirty = false;
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;
                    }
                });
            },
        },
    };
</script>
