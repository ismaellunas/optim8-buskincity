<template>
    <app-layout>
        <template #header>
            Add New Category
        </template>

        <biz-error-notifications :errors="$page.props.errors" />

        <div class="box mb-6">
            <category-form
                v-model="form[selectedLocale]"
                :base-route="baseRoute"
                :default-locale="defaultLocale"
                :errors="errors"
                :is-input-disabled="isProcessing"
                :is-new="isNew"
                :locale-options="localeOptions"
                :selected-locale="selectedLocale"
                @change-locale="changeLocale"
                @on-submit="submit"
            />
        </div>
    </app-layout>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import CategoryForm from '@/Pages/Category/Form';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { usePage, useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            BizErrorNotifications,
            CategoryForm,
        },

        mixins: [
            MixinHasLoader
        ],

        props: {
            baseRoute: { type: String, required: true },
            errors: { type: Object, default:() => {} },
        },

        setup() {
            const defaultLocale = usePage().props.value.defaultLanguage;
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
                localeOptions: usePage().props.value.languageOptions,
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
                    route(self.baseRoute + '.store'),
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

            changeLocale() {
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
