<template>
    <app-layout>
        <template #header>
            Update Page
        </template>

        <sdb-error-notifications :errors="$page.props.errors"/>

        <sdb-flash-notifications :flash="$page.props.flash"/>

        <div class="box mb-6">
            <page-form
                v-model="form.translations[selectedLocale]"
                v-model:content-config-id="contentConfigId"
                :can="can"
                :errors="errors"
                :isNew="isNew"
                :isEditMode="isEditMode"
                :statusOptions="statusOptions"
                :tabActive="tabActive"
                :localeOptions="localeOptions"
                :selectedLocale="selectedLocale"
                @change-locale="onChangeLocale"
                @on-submit="onSubmit"
            />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import PageForm from '@/Pages/Page/Form';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbFlashNotifications from '@/Sdb/FlashNotifications';
    import { getEmptyPageTranslation } from '@/Libs/page';
    import { getTranslation } from '@/Libs/translation';
    import { isBlank } from '@/Libs/utils';
    import { onPageEditorClicked } from '@/Libs/page-builder';
    import { ref, onMounted, onUnmounted } from 'vue';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            PageForm,
            SdbErrorNotifications,
            SdbFlashNotifications,
        },
        props: {
            can: Object,
            page: Object,
            errors: Object,
            tabActive: {default: 0},
            // options:
            statusOptions: {type: Array, default: []},
        },
        setup(props) {
            const translationForm = { translations: {} };
            const defaultLocale = usePage().props.value.defaultLanguage;

            let translatedPage = getTranslation(props.page, defaultLocale);

            if (isBlank(translatedPage)) {
                translatedPage = getEmptyPageTranslation();
            }

            translationForm.translations[defaultLocale] = JSON.parse(JSON.stringify(translatedPage));

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
                this.form.put(submitRoute);
            },
            onChangeLocale(locale) {
                if (this.form.isDirty) {
                    const confirmationMessage = (
                        'It looks like you have been editing something. '
                        + 'If you leave before saving, your changes will be lost.'
                    );

                    this.$swal.fire({
                        title: 'Are you sure?',
                        text: confirmationMessage,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Leave this',
                        cancelButtonText: 'Continue Editing',
                        scrollbarPadding: false,
                    }).then((result) => {
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

                let translationFrom = { translations: {} };

                if (isBlank(translatedPage)) {
                    translationFrom.translations[locale] = getEmptyPageTranslation();
                } else {
                    translationFrom.translations[locale] = JSON.parse(JSON.stringify(translatedPage));
                }
                this.form = useForm(translationFrom);
            },
        },
    }
</script>
