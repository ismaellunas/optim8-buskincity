<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <div class="box mb-6">
            <page-form
                v-model="form[selectedLocale]"
                v-model:content-config-id="contentConfigId"
                :errors="errors"
                :is-dirty="form.isDirty"
                :is-new="isNew"
                :locale-options="localeOptions"
                :page-preview="true"
                :selected-locale="selectedLocale"
                :status-options="statusOptions"
                :empty-page-locale-options="emptyPageLocaleOptions"
                @on-change-locale="onChangeLocale"
                @on-submit="onSubmit"
                @on-delete-translation="onDeleteTranslation"
                @on-duplicate-translation="onDuplicateTranslation"
            />
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import PageForm from '@/Pages/Page/Form.vue';
    import { confirmDelete, confirmLeaveProgress, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { find } from 'lodash';
    import { getEmptyPageTranslation } from '@/Libs/page';
    import { getTranslation } from '@/Libs/translation';
    import { isBlank } from '@/Libs/utils';
    import { onPageEditorClicked } from '@/Libs/page-builder';
    import { pageStatus } from '@/Libs/defaults';
    import { ref, onMounted, onUnmounted } from 'vue';
    import { useForm, usePage } from '@inertiajs/vue3';

    export default {
        name: 'PageEdit',

        components: {
            BizErrorNotifications,
            PageForm,
        },

        mixins: [
            MixinHasLoader,
        ],

        provide() {
            return {
                can: this.can,
                i18n: this.i18n,
                instructions: this.instructions,
                media: this.media,
                settingOptions: this.settingOptions,
            }
        },

        layout: AppLayout,

        props: {
            affectedFooterMenu: { type: Object, default:() => {} },
            affectedHeaderMenu: { type: Object, default:() => {} },
            can: { type: Object, required: true },
            errors: { type: Object, default:() => {} },
            i18n: { type: Object, default: () => {} },
            instructions: { type: Object, default:() => {} },
            media: { type: Object, default: () => {} },
            page: { type: Object, required: true },
            settingOptions: { type: Object, default:() => {} },
            statusOptions: { type: Array, default:() => [] },
            title: { type: String, required: true },
        },

        setup(props) {
            const defaultLocale = usePage().props.defaultLanguage;
            const translationForm = { [defaultLocale]: {} };

            let translatedPage = getTranslation(props.page, defaultLocale);

            if (isBlank(translatedPage)) {
                translatedPage = getEmptyPageTranslation();
            }

            translationForm[defaultLocale] = JSON.parse(JSON.stringify(translatedPage));

            const contentConfigId = ref('');

            function pageListener(event) {
                onPageEditorClicked(event, contentConfigId);
            }

            onMounted(() => {
                window.addEventListener("click", pageListener);
            });

            onUnmounted(() => {
                window.removeEventListener("click", pageListener);
            });

            return {
                contentConfigId,
                defaultLocale,
                footerMenuItems: props.footerMenuItems,
                form: useForm(translationForm),
                headerMenuItems: props.headerMenuItems,
                localeOptions: usePage().props.languageOptions,
            };
        },

        data() {
            return {
                disableInput: false,
                isNew: false,
                selectedLocale: this.defaultLocale,
            };
        },

        computed: {
            selectedLocaleName() {
                return find(this.localeOptions, { id: this.selectedLocale }).name ?? '';
            },

            emptyPageLocaleOptions() {
                const self = this;

                return self.localeOptions.map(function(locale) {
                    if (!getTranslation(self.page, locale.id)) {
                        return locale;
                    }

                    return null;
                }).filter(Boolean);
            },
        },

        methods: {
            isUsedByMenu() {
                const self = this;

                return new Promise((resolve, reject) => {
                    const url = route('admin.api.pages.is-used-by-menu', {
                        page: self.page.id,
                        locale: self.selectedLocale,
                    });

                    axios.get(url)
                        .then(response => {
                            resolve(response.data == true);
                        })
                        .catch(error => {
                            reject(error);
                        })
                });
            },

            async canSavePage() {
                try {
                    let translatedPage = getTranslation(
                        this.page,
                        this.selectedLocale
                    );

                    const pageTranslationStatus = this.form[this.selectedLocale]['status'];

                    if (
                        translatedPage
                        && translatedPage.status == pageStatus.published
                        && pageTranslationStatus == pageStatus.draft
                    ) {
                        const isUsedByMenu = await this.isUsedByMenu();

                        if (isUsedByMenu) {
                            const confirmResult = await confirmDelete(
                                this.i18n.are_you_sure,
                                this.i18n.affected_menu_warning,
                                this.i18n.yes,
                            );

                            return !!confirmResult.value;
                        }
                    }

                    return true;

                } catch (error) {
                    console.error(error);

                    return true;
                }
            },

            async onSubmit() {
                if (await this.canSavePage()) {
                    const submitRoute = route('admin.pages.update', {id: this.page.id});

                    this.form.put(submitRoute, {
                        onStart: this.onStartLoadingOverlay,
                        onFinish: this.onEndLoadingOverlay,
                        onSuccess: (page) => {
                            const translatedPage = getTranslation(
                                this.page,
                                this.selectedLocale
                            );

                            this.form[this.selectedLocale]['id'] = translatedPage.id;

                            successAlert(page.props.flash.message);
                        },
                    });
                }
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
                let translatedPage = getTranslation(this.page, locale);

                let translationForm = { [this.defaultLocale]: {} };

                if (isBlank(translatedPage) || typeof translatedPage == 'undefined') {
                    translationForm[locale] = getEmptyPageTranslation();
                } else {
                    translationForm[locale] = JSON.parse(JSON.stringify(translatedPage));
                }

                this.form = useForm(translationForm);
            },
            onDeleteTranslation() {
                const self = this;

                confirmDelete(
                    'Are you sure want to delete the '+ self.selectedLocaleName + ' translation?',
                ).then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route('admin.pages.translations.destroy', self.form[self.selectedLocale]?.id),
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: () => {
                                    oopsAlert();
                                },
                                onSuccess: (page) => {
                                    successAlert(page.props.flash.message);

                                    self.changeLocale(self.defaultLocale);
                                },
                            }
                        );
                    }
                })
            },
            onDuplicateTranslation(data) {
                if (this.form.isDirty) {

                    confirmLeaveProgress().then((result) => {
                        if (result.isDismissed) {
                            return false;
                        } else if(result.isConfirmed) {
                            this.duplicateTranslation(data);
                        }
                    })
                } else {
                    this.duplicateTranslation(data);
                }
            },
            duplicateTranslation(data) {
                this.changeLocale(data.locale);

                const form = this.form[this.selectedLocale];

                Object.keys(data.form).forEach(attribute => {
                    form[attribute] = data.form[attribute];
                });
            },
        },
    }
</script>
