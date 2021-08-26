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
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            PageForm,
            SdbErrorNotifications,
        },
        props: {
            page: Object,
            errors: Object,
            tabActive: String,
            defaultLocale: {default: 'en'},
            // options:
            localeOptions: {type: Array, default: []},
            statusOptions: {type: Array, default: []},
        },
        setup(props) {
            const translationForm = {
                translations: {
                    en: getEmptyPageTranslation()
                }
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
                form: useForm(translationForm),
                contentConfigId
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
            changeLocale(locale) {
                const confirmationMessage = (
                    'It looks like you have been editing something. '
                    + 'If you leave before saving, your changes will be lost.'
                );

                this.$swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please provide English (EN) translation first!'
                })
            },
        }
    }
</script>
