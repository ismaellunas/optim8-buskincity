<template>
    <app-layout>
        <template #header>
            Update Page
        </template>

        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <biz-flash-notifications :flash="$page.props.flash" />

        <div class="box mb-6">
            <page-form
                v-model="form[selectedLocale]"
                v-model:content-config-id="contentConfigId"
                :can="can"
                :errors="errors"
                :is-new="isNew"
                :is-edit-mode="isEditMode"
                :status-options="statusOptions"
                :locale-options="localeOptions"
                :selected-locale="selectedLocale"
                @change-locale="onChangeLocale"
                @on-submit="onSubmit"
            />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import PageForm from '@/Pages/Page/Form';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFlashNotifications from '@/Biz/FlashNotifications';
    import { getEmptyPageTranslation } from '@/Libs/page';
    import { getTranslation } from '@/Libs/translation';
    import { isBlank } from '@/Libs/utils';
    import { onPageEditorClicked } from '@/Libs/page-builder';
    import { ref, onMounted, onUnmounted } from 'vue';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import { confirmDelete, confirmLeaveProgress } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            PageForm,
            BizErrorNotifications,
            BizFlashNotifications,
        },
        props: {
            can: { type: Object, required: true },
            page: { type: Object, required: true },
            errors: { type: Object, default:() => {} },
            statusOptions: { type: Array, default:() => [] },
            affectedHeaderMenu: { type: Object, default:() => {} },
            affectedFooterMenu: { type: Object, default:() => {} }
        },
        setup(props) {
            const defaultLocale = usePage().props.value.defaultLanguage;
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
                form: useForm(translationForm),
                localeOptions: usePage().props.value.languageOptions,
                headerMenuItems: props.headerMenuItems,
                footerMenuItems: props.footerMenuItems,
            };
        },
        data() {
            return {
                disableInput: false,
                isEditMode: true,
                isNew: false,
                selectedLocale: this.defaultLocale,
            };
        },
        methods: {
            onSubmit() {
                const submitRoute = route('admin.pages.update', {id: this.page.id});
                if (
                    this.affectedHeaderMenu[this.selectedLocale] === true
                    || this.affectedFooterMenu[this.selectedLocale] === true
                ) {
                    if (this.form[this.selectedLocale].status === 0) {
                        confirmDelete(
                            'Are You Sure?',
                            'This action will also remove the page on the navigation menu.',
                            'Yes'
                        ).then((result) => {
                            if (result.isDismissed) {
                                return false;
                            } else if(result.isConfirmed) {
                                this.form.put(submitRoute, {
                                onSuccess: () => {
                                    const translatedPage = getTranslation(
                                        this.page,
                                        this.selectedLocale
                                    );

                                    this.form[this.selectedLocale]['id'] = translatedPage.id;
                                },
                            });
                            }
                        })
                    } else {
                        this.form.put(submitRoute, {
                            onSuccess: () => {
                                const translatedPage = getTranslation(
                                    this.page,
                                    this.selectedLocale
                                );

                                this.form[this.selectedLocale]['id'] = translatedPage.id;
                            },
                        });
                    }
                } else {
                    this.form.put(submitRoute, {
                        onSuccess: () => {
                            const translatedPage = getTranslation(
                                this.page,
                                this.selectedLocale
                            );

                            this.form[this.selectedLocale]['id'] = translatedPage.id;
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
                const translatedPage = getTranslation(this.page, locale);

                let translationFrom = { [this.defaultLocale]: {} };

                if (isBlank(translatedPage)) {
                    translationFrom[locale] = getEmptyPageTranslation();
                } else {
                    translationFrom[locale] = JSON.parse(JSON.stringify(translatedPage));
                }
                this.form = useForm(translationFrom);
            }
        },
    }
</script>
