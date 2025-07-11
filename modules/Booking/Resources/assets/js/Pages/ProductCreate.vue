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
                        :image-mimes="imageMimes"
                        :instructions="instructions"
                        :role-options="roleOptions"
                        :rules="rules"
                        :status-options="statusOptions"
                    />

                    <hr>

                    <div class="field is-grouped is-grouped-right">
                        <div class="control">
                            <biz-button-link
                                :href="route(baseRouteName+'.index')"
                                class="is-link is-light"
                            >
                                {{ i18n.cancel }}
                            </biz-button-link>
                        </div>
                        <div class="control">
                            <biz-button class="is-link">
                                {{ i18n.create }}
                            </biz-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import ProductForm from './ProductForm.vue';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';

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

        provide() {
            return {
                can: this.can,
                i18n: this.i18n,
                dimensions: this.dimensions,
            };
        },

        layout: AppLayout,

        props: {
            baseRouteName: {type: String, required: true},
            can: { type: Object, required: true },
            dimensions: { type: Object, default: () => {} },
            imageMimes: {type: Array, required: true },
            instructions: {type: Object, default: () => {}},
            roleOptions: { type: Array, required: true },
            rules: { type: Object, required: true },
            statusOptions: { type: Array, required: true },
            i18n: { type: Object, default: () => ({
                cancel : 'Cancel',
                create : 'Create',
            }) },
        },

        setup() {
            const form = {
                name: null,
                status: 'draft',
                description: null,
                short_description: null,
                roles: null,
                is_check_in_required: false,
                gallery: [],
            };

            return {
                form: useForm(form),
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
