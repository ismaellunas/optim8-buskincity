<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="mb-6">
            <form
                method="post"
                @submit.prevent="submit"
            >
                <div class="box">
                    <product-form
                        v-model="form"
                        :role-options="roleOptions"
                        :status-options="statusOptions"
                    />

                    <hr>

                    <div class="field is-grouped is-grouped-right">
                        <div class="control">
                            <biz-button-link
                                :href="route(baseRouteName+'.index')"
                                class="is-link is-light"
                            >
                                Cancel
                            </biz-button-link>
                        </div>
                        <div class="control">
                            <biz-button class="is-link">
                                Create
                            </biz-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import ProductForm from './ProductForm';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            ProductForm,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: {type: String, required: true},
            roleOptions: { type: Array, required: true },
            statusOptions: { type: Array, required: true },
            imageMimes: {type: Array, required: true },
        },

        setup(props, { emit }) {
            const form = {
                name: null,
                status: 'draft',
                description: null,
                short_description: null,
                roles: null,
                gallery: {
                    deleted_media: [],
                    files: [],
                },
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
            };
        },

        methods: {
            submit() {
                const self = this;
                const url = route(this.baseRouteName + '.store');

                this.form.post(url, {
                    onStart: self.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: self.onEndLoadingOverlay,
                });
            },
        },
    };
</script>
