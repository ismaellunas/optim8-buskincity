<template>
    <app-layout :title="title">
        <template #header>
            {{ title }}
        </template>

        <div class="box mb-6">
            <biz-provide-inject-tabs
                v-model="activeTab"
                class="is-boxed"
            >
                <biz-provide-inject-tab title="Space">
                    <form
                        action=""
                        @submit.prevent="submit"
                    >
                        <space-form
                            v-model="space"
                            :country-options="countryOptions"
                            :cover-url="coverUrl"
                            :default-country="defaultCountry"
                            :instructions="instructions"
                            :logo-url="logoUrl"
                            :parent-options="parentOptions"
                            :type-options="typeOptions"
                        >
                            <biz-form-select
                                v-model="space.is_page_enabled"
                                label="Is Page Enabled ?"
                            >
                                <option
                                    v-for="(optValue, optKey) in pageEnabledOptions"
                                    :key="optKey"
                                    :value="optValue"
                                    class="is-capitalize"
                                >
                                    {{ optKey }}
                                </option>
                            </biz-form-select>

                            <space-form-translatable
                                v-model="space"
                                class="py-2"
                            />

                            <template #action>
                                <div class="field is-grouped is-grouped-right mt-4">
                                    <div class="control">
                                        <biz-button-link
                                            :href="routeIndex"
                                            class="is-link is-light"
                                        >
                                            Cancel
                                        </biz-button-link>
                                    </div>
                                    <div class="control">
                                        <biz-button class="is-link">
                                            Update
                                        </biz-button>
                                    </div>
                                </div>
                            </template>
                        </space-form>
                    </form>
                </biz-provide-inject-tab>

                <biz-provide-inject-tab title="Manager">
                    <form @submit.prevent="submitManager">
                        <space-manager
                            v-model="managers"
                        >
                            <template #action>
                                <div class="field is-grouped is-grouped-right mt-4">
                                    <div class="control">
                                        <biz-button class="is-link">
                                            Update
                                        </biz-button>
                                    </div>
                                </div>
                            </template>
                        </space-manager>
                    </form>
                </biz-provide-inject-tab>

                <biz-provide-inject-tab
                    title="Page"
                    :is-rendered="spaceRecord.is_page_enabled"
                >
                    <page-form
                        v-model="pageForm[selectedLocale]"
                        v-model:content-config-id="contentConfigId"
                        :errors="errors"
                        :is-dirty="pageForm.isDirty"
                        :is-new="isPageNew"
                        :is-page-builder-rendered="false"
                        :locale-options="localeOptions"
                        :page-preview="true"
                        :page-preview-url="pagePreviewUrl"
                        :selected-locale="selectedLocale"
                        :status-options="statusOptions"
                        @change-locale="onChangeLocale"
                    >
                        <template #action="pageFormProps">
                            <div class="field is-grouped is-grouped-right">
                                <div class="control">
                                    <biz-button-link
                                        class="is-link is-light"
                                        :href="route('admin.spaces.index')"
                                    >
                                        Cancel
                                    </biz-button-link>
                                </div>
                                <div class="control">
                                    <biz-button
                                        class="is-link"
                                        @click="submitPage"
                                    >
                                        {{ pageFormProps.isNew ? 'Create' : 'Update' }}
                                    </biz-button>
                                </div>
                            </div>
                        </template>
                    </page-form>
                </biz-provide-inject-tab>
            </biz-provide-inject-tabs>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasTab from '@/Mixins/HasTab';
    import PageForm from '@/Pages/Page/Form';
    import SpaceForm from './SpaceForm';
    import SpaceFormTranslatable from './SpaceFormTranslatable';
    import SpaceManager from './SpaceManager';
    import { confirmLeaveProgress, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { getEmptyPageTranslation } from '@/Libs/page';
    import { getTranslation } from '@/Libs/translation';
    import { isBlank } from '@/Libs/utils';
    import { pick, map } from 'lodash';
    import { ref } from "vue";
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonLink,
            BizFormSelect,
            BizProvideInjectTab,
            BizProvideInjectTabs,
            SpaceForm,
            SpaceFormTranslatable,
            SpaceManager,
            PageForm,
        },

        mixins: [
            MixinHasLoader,
            MixinHasTab,
        ],

        provide() {
            return {
                can: this.can,
            }
        },

        props: {
            baseRouteName: { type: String, default: '' },
            can: { type: Object, required: true },
            countryOptions: { type: Array, default:() => [] },
            coverUrl: { type: String, default: '' },
            defaultCountry: { type: String, required: true },
            errors: { type: Object, default:() => {} },
            images: { type: Object, required: true },
            instructions: { type: Object, required: true },
            logoUrl: { type: String, default: '' },
            page: { type: Object, required: true },
            parentOptions: { type: Object, default: () => {} },
            spaceManagers: { type: Array, default: () => [] },
            spaceRecord: { type: Object, required: true },
            statusOptions: { type: Array, default:() => [] },
            tab: { type: Number, default: 0 },
            title: { type: String, default: "" },
            typeOptions: { type: Object, default: () => {} },
        },

        setup(props) {
            const defaultLocale = usePage().props.value.defaultLanguage;
            const translationForm = { [defaultLocale]: {} };

            let translatedPage = getTranslation(props.page, defaultLocale);

            if (isBlank(translatedPage)) {
                translatedPage = getEmptyPageTranslation();
            }

            translationForm[defaultLocale] = JSON.parse(JSON.stringify(translatedPage));

            return {
                activeTab: ref(props.tab),
                routeIndex: route(props.baseRouteName+'.index'),
                pageEnabledOptions: { No: false, Yes: true },
                contentConfigId: ref(''),
                defaultLocale,
                localeOptions: usePage().props.value.languageOptions,
                pageForm: useForm(translationForm),
            };
        },

        data() {
            return {
                managers: this.spaceManagers,
                space: {},
                selectedLocale: this.defaultLocale,
                pagePreviewUrl: null,
            };
        },

        computed: {
            isPageNew() {
                return !this.page?.id;
            },
        },

        beforeMount() {
            this.setSpace();
        },

        mounted() {
            this.setPagePreviewUrl(this.pageForm[this.defaultLocale]);
        },

        methods: {
            submit() {
                const self = this;
                const url = route(self.baseRouteName+'.update', self.spaceRecord.id);

                self.space
                    .transform((data) => ({
                        ...data,
                        _method: 'put',
                    }))
                    .post(url, {
                        onStart: self.onStartLoadingOverlay,
                        onSuccess: (page) => {
                            self.space.deleted_media = {};
                            successAlert(page.props.flash.message);
                        },
                        onError: () => { oopsAlert() },
                        onFinish: () => {
                            self.onEndLoadingOverlay();
                        }
                    });
            },

            submitManager() {
                const self = this;
                const form = useForm({
                    managers: map(this.managers, 'id')
                });

                form.post(route(self.baseRouteName+'.update-managers', self.spaceRecord.id), {
                    replace: true,
                    onStart: self.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: self.onEndLoadingOverlay
                });
            },

            setSpace() {
                const space = pick(this.spaceRecord, [
                    'id',
                    'address',
                    'contacts',
                    'is_page_enabled',
                    'latitude',
                    'longitude',
                    'name',
                    'parent_id',
                    'translations',
                    'type',
                ]);

                space['logo'] = null;
                space['cover'] = null;
                space['_method'] = 'put';
                space['deleted_media'] = {};

                this.space = useForm(space);
            },

            onChangeLocale(locale) {
                if (this.isPageNew) {
                    const locale = this
                        .localeOptions
                        .find(localeOption => localeOption.id == this.defaultLocale);

                    oopsAlert({
                        text: (
                            'Please provide '
                            +locale.name+' ('+locale.id.toUpperCase()+') translation first!'
                        ),
                    });

                } else if (this.pageForm.isDirty) {

                    confirmLeaveProgress().then((result) => {
                        if (result.isDismissed) {
                            return false;
                        } else if (result.isConfirmed) {
                            this.changeLocale(locale);
                        }
                    });

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

                this.pageForm = useForm(translationFrom);
                this.setPagePreviewUrl(translationFrom[locale]);
            },

            submitPage() {
                let method = null;
                let url = null;

                if (! this.page?.id) {
                    method = 'post';
                    url = route('admin.spaces.pages.store', this.spaceRecord.id);

                } else {

                    method = 'put';
                    url = route(
                        'admin.spaces.pages.update',
                        [this.spaceRecord.id, this.page.id]
                    );
                }

                const options = {
                    replace: true,
                    onStart: this.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        const translatedPage = getTranslation(
                            this.page,
                            this.selectedLocale
                        );

                        this.pageForm[this.selectedLocale]['id'] = translatedPage.id;

                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: () => {
                        this.setTranslationForm(this.selectedLocale);
                        this.onEndLoadingOverlay();
                    }
                };

                this.pageForm.submit(method, url, options);
            },

            setPagePreviewUrl(page) {
                this.pagePreviewUrl = page.landing_page_space_url + `?&preview`;
            },
        },
    };
</script>
