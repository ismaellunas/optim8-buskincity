<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="box mb-6">
            <category-form
                v-model="form[selectedLocale]"
                :base-route="baseRouteName"
                :default-locale="defaultLocale"
                :errors="errors"
                :is-input-disabled="isProcessing"
                :is-new="isNew"
                :locale-options="localeOptions"
                :selected-locale="selectedLocale"
                @on-change-locale="onChangeLocale"
                @on-submit="submit"
            />
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import CategoryForm from '@/Pages/Category/Form.vue';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { usePage, useForm } from '@inertiajs/vue3';

    export default {
        name: 'CategoryCreate',

        components: {
            BizErrorNotifications,
            CategoryForm,
        },

        mixins: [
            MixinHasLoader
        ],

        provide() {
            return {
                i18n: this.i18n,
            }
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            errors: { type: Object, default:() => {} },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => {} }
        },

        setup() {
            const defaultLocale = usePage().props.defaultLanguage;
            const translationForm = {
                [defaultLocale]: {
                    name: null,
                    slug: null,
                    meta_title: null,
                    meta_description: null,
                }
            };

            return {
                defaultLocale: defaultLocale,
                form: useForm(translationForm),
                localeOptions: usePage().props.languageOptions,
            };
        },

        data() {
            return {
                isNew: true,
                isProcessing: false,
                selectedLocale: this.defaultLocale,
            };
        },

        methods: {
            submit() {
                const self = this;

                self.form.post(
                    route(self.baseRouteName + '.store'),
                    {
                        onStart: () => {
                            self.onStartLoadingOverlay();
                            self.isProcessing = true;
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                        },
                        onFinish: () => {
                            self.onEndLoadingOverlay();
                            self.isProcessing = false;
                        }
                    }
                );
            },

            onChangeLocale() {
                let locale = {};
                this.localeOptions.map(localeOption => {
                    if (localeOption.id == this.defaultLocale) {
                        locale = localeOption;
                    }
                });

                oopsAlert({
                    text: 'Please provide '+locale.name+' ('+locale.id.toUpperCase()+') translation first!',
                });
            },
        },
    };
</script>
