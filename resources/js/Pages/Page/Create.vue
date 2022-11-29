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
                :is-new="isNew"
                :status-options="statusOptions"
                :locale-options="localeOptions"
                :selected-locale="selectedLocale"
                @on-change-locale="onChangeLocale"
                @on-submit="onSubmit"
            />
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout';
    import PageForm from '@/Pages/Page/Form';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import { getEmptyPageTranslation } from '@/Libs/page';
    import { onPageEditorClicked } from '@/Libs/page-builder';
    import { oops as oopsAlert } from '@/Libs/alert';
    import { ref, onMounted, onUnmounted } from 'vue';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            PageForm,
            BizErrorNotifications,
        },

        mixins: [
            MixinHasLoader,
        ],

        provide() {
            return {
                can: this.can,
                media: this.media,
            }
        },

        layout: AppLayout,

        props: {
            can: { type: Object, required: true },
            errors: { type: Object, default:() => {} },
            page: { type: Object, required: true },
            statusOptions: { type: Array, default:() => [] },
            title: { type: String, required: true },
            media: { type: Object, default: () => {} },
        },

        setup() {
            const defaultLocale = usePage().props.value.defaultLanguage;
            const translations = {};

            translations[defaultLocale] = getEmptyPageTranslation();

            const translationForm = { [defaultLocale]: getEmptyPageTranslation() };

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
                isNew: true,
                selectedLocale: this.defaultLocale,
            };
        },

        methods: {
            onSubmit() {
                const submitRoute = route('admin.pages.store');
                this.form.post(submitRoute, {
                    onStart: this.onStartLoadingOverlay,
                    onFinish: this.onEndLoadingOverlay,
                });
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
        }
    }
</script>
