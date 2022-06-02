<template>
    <app-layout>
        <template #header>
            Edit Category
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
                @change-locale="onChangeLocale"
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
    import { getTranslation } from '@/Libs/translation';
    import { isBlank } from '@/Libs/utils';
    import { success as successAlert, confirmLeaveProgress } from '@/Libs/alert';
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
            record: { type: Object, required: true },
        },

        setup(props) {
            const defaultLocale = usePage().props.value.defaultLanguage;
            const translationForm = { [defaultLocale]: {} };

            let translatedCategory = getTranslation(props.record, defaultLocale);

            if (isBlank(translatedCategory)) {
                translatedCategory = {
                    name: null,
                    slug: null,
                    meta_title: null,
                    meta_description: null,
                };
            }

            translationForm[defaultLocale] = translatedCategory;

            return {
                defaultLocale: defaultLocale,
                localeOptions: usePage().props.value.languageOptions,
                form: useForm(translationForm),
            };
        },

        data() {
            return {
                isNew: false,
                isProcessing: false,
                selectedLocale: this.defaultLocale,
            };
        },

        methods: {
            submit() {
                const self = this;

                self.form.put(
                    route(self.baseRoute + '.update', self.record.id),
                    {
                        onStart: () => {
                            self.onStartLoadingOverlay();
                            self.isProcessing = true;
                        },
                        onSuccess: (page) => {
                            self.setTranslationForm(self.selectedLocale);

                            successAlert(page.props.flash.message);
                        },
                        onFinish: () => {
                            self.onEndLoadingOverlay();
                            self.isProcessing = false;
                        },
                    }
                );
            },

            onChangeLocale(locale) {
                if (this.form.isDirty) {

                    confirmLeaveProgress().then((result) => {
                        if (result.isDismissed) {
                            return false;
                        } else if(result.isConfirmed) {
                            this.changeLocale(locale);
                        }
                    })
                } else {
                    this.changeLocale(locale);
                }
            },

            changeLocale(locale) {
                this.setTranslationForm(locale);
                this.selectedLocale = locale;
            },

            setTranslationForm(locale) {
                const translatedCategory = getTranslation(this.record, locale);

                let translationFrom = { [this.defaultLocale]: {} };

                if (isBlank(translatedCategory)) {
                    translationFrom[locale] = {
                        name: null,
                        slug: null,
                    };
                } else {
                    translationFrom[locale] = translatedCategory;
                }
                this.form = useForm(translationFrom);
            }
        },
    };
</script>
