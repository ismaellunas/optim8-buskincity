<template>
    <app-layout>
        <template #header>
            Create New Page
        </template>

        <sdb-error-notifications :errors="$page.props.errors"/>

        <div class="box mb-6">
            <page-form
                v-model="form.translations[selectedLocale]"
                v-model:content-config-id="contentConfigId"
                :can="can"
                :errors="errors"
                :isNew="isNew"
                :isEditMode="isEditMode"
                :statusOptions="statusOptions"
                :localeOptions="localeOptions"
                :selectedLocale="selectedLocale"
                @change-locale="changeLocale"
                @on-submit="onSubmit"
            />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import PageForm from '@/Pages/Page/Form';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import { getEmptyPageTranslation } from '@/Libs/page';
    import { onPageEditorClicked } from '@/Libs/page-builder';
    import { ref, onMounted, onUnmounted } from 'vue';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            PageForm,
            SdbErrorNotifications,
        },
        props: {
            can: Object,
            page: Object,
            errors: Object,
            tabActive: String,
            statusOptions: {type: Array, default: []},
        },
        setup() {
            const defaultLocale = usePage().props.value.defaultLanguage;
            const translations = {};

            translations[defaultLocale] = getEmptyPageTranslation();

            const translationForm = {
                translations
            };

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
                defaultLocale: defaultLocale,
                form: useForm(translationForm),
                localeOptions: usePage().props.value.languageOptions,
            };
        },
        data() {
            return {
                disableInput: false,
                isEditMode: true,
                isNew: true,
                selectedLocale: this.defaultLocale,
            };
        },
        methods: {
            onSubmit() {
                const submitRoute = route('admin.pages.store');
                this.form.post(submitRoute);
            },
            changeLocale() {
                let locale = {};
                this.localeOptions.map(localeOption => {
                    if (localeOption.id == this.defaultLocale) {
                        locale = localeOption;
                    }
                });

                this.$swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please provide '+locale.name+' ('+locale.id.toUpperCase()+') translation first!'
                })
            },
        }
    }
</script>
