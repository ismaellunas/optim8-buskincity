<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <biz-flash-notifications :flash="$page.props.flash" />

        <div class="box mb-6">
            <page-form
                v-model="form[selectedLocale]"
                v-model:content-config-id="contentConfigId"
                :errors="errors"
                :is-dirty="form.isDirty"
                :is-edit-mode="isEditMode"
                :is-new="isNew"
                :locale-options="localeOptions"
                :page-preview="true"
                :selected-locale="selectedLocale"
                :status-options="statusOptions"
                @on-change-locale="onChangeLocale"
                @on-submit="onSubmit"
            />
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFlashNotifications from '@/Biz/FlashNotifications';
    import PageForm from '@/Pages/Page/Form';
    import { confirmDelete, confirmLeaveProgress } from '@/Libs/alert';
    import { getEmptyPageTranslation } from '@/Libs/page';
    import { getTranslation } from '@/Libs/translation';
    import { isBlank } from '@/Libs/utils';
    import { onPageEditorClicked } from '@/Libs/page-builder';
    import { pageStatus } from '@/Libs/defaults';
    import { ref, onMounted, onUnmounted } from 'vue';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizErrorNotifications,
            BizFlashNotifications,
            PageForm,
        },

        provide() {
            return {
                can: this.can,
            }
        },

        layout: AppLayout,

        props: {
            affectedFooterMenu: { type: Object, default:() => {} },
            affectedHeaderMenu: { type: Object, default:() => {} },
            can: { type: Object, required: true },
            errors: { type: Object, default:() => {} },
            page: { type: Object, required: true },
            statusOptions: { type: Array, default:() => [] },
            title: { type: String, required: true },
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
                footerMenuItems: props.footerMenuItems,
                form: useForm(translationForm),
                headerMenuItems: props.headerMenuItems,
                localeOptions: usePage().props.value.languageOptions,
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
                                'Are You Sure?',
                                'This action will also remove the page on the navigation menu.',
                                'Yes'
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
