<template>
    <app-layout
        :breadcrumbs="breadcrumbs"
        :title="title"
    >
        <template #sideBreadcrumbs>
            <p class="buttons is-right">
                <biz-button-icon
                    :disabled="!canPreviewPage"
                    :icon="iconPreview"
                    @click="previewPage"
                >
                    <span>Page Preview</span>
                </biz-button-icon>
            </p>
        </template>

        <div>
            <div class="box mb-6">
                <biz-provide-inject-tabs
                    v-model="activeTab"
                    class="is-boxed"
                >
                    <biz-provide-inject-tab :title="i18n.space">
                        <form
                            action=""
                            @submit.prevent="submit"
                        >
                            <space-form
                                v-model="space"
                                :cover-media="coverMedia"
                                :default-country="defaultCountry"
                                :instructions="instructions"
                                :logo-media="logoMedia"
                                :parent-options="parentOptions"
                                :type-options="typeOptions"
                                :can-change-parent="can.changeParent"
                            >
                                <biz-form-select
                                    v-if="can.page.edit"
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

                    <biz-provide-inject-tab
                        title="Event"
                        :is-rendered="isEventRendered"
                    >
                        <space-event
                            :space="space"
                        />
                    </biz-provide-inject-tab>

                    <biz-provide-inject-tab
                        title="Manager"
                        :is-rendered="isManagerRendered"
                    >
                        <form
                            class="box"
                            @submit.prevent="submitManager"
                        >
                            <biz-form-assign-user
                                v-model="managers"
                                label="Choose Manager"
                                :get-users-url="route('admin.spaces.search-managers')"
                            />

                            <div class="field is-grouped is-grouped-right mt-4">
                                <div class="control">
                                    <biz-button class="is-link">
                                        Update
                                    </biz-button>
                                </div>
                            </div>
                        </form>
                    </biz-provide-inject-tab>

                    <biz-provide-inject-tab
                        title="Page"
                        :is-rendered="isPageRendered"
                    >
                        <page-form
                            v-model="pageForm[selectedLocale]"
                            v-model:content-config-id="contentConfigId"
                            :errors="errors"
                            :is-dirty="pageForm.isDirty"
                            :is-new="isPageNew"
                            :is-page-builder-rendered="false"
                            :is-page-setting-rendered="false"
                            :locale-options="localeOptions"
                            :selected-locale="selectedLocale"
                            :status-options="statusOptions"
                            @on-change-locale="onChangeLocale"
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
        </div>
    </app-layout>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasTab from '@/Mixins/HasTab';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizFormAssignUser from '@/Biz/Form/AssignUser.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab.vue';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs.vue';
    import PageForm from '@/Pages/Page/Form.vue';
    import SpaceEvent from './SpaceEvent.vue';
    import SpaceForm from './SpaceForm.vue';
    import SpaceFormTranslatable from './SpaceFormTranslatable.vue';
    import { cloneDeep, pick, map } from 'lodash';
    import { confirmDelete, confirmLeaveProgress, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { getEmptyPageTranslation } from '@/Libs/page';
    import { getTranslation } from '@/Libs/translation';
    import { isBlank } from '@/Libs/utils';
    import { pageStatus } from '@/Libs/defaults';
    import { preview as iconPreview } from '@/Libs/icon-class';
    import { ref } from "vue";
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizFormAssignUser,
            BizFormSelect,
            BizProvideInjectTab,
            BizProvideInjectTabs,
            PageForm,
            SpaceEvent,
            SpaceForm,
            SpaceFormTranslatable,
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
            breadcrumbs: { type: Array, default: () => [] },
            can: { type: Object, required: true },
            coverMedia: { type: Object, default: () => {} },
            defaultCountry: { type: String, required: true },
            errors: { type: Object, default:() => {} },
            instructions: { type: Object, required: true },
            logoMedia: { type: Object, default: () => {} },
            page: { type: Object, required: true },
            parentOptions: { type: Object, default: () => {} },
            spaceManagers: { type: Array, default: () => [] },
            spaceRecord: { type: Object, required: true },
            statusOptions: { type: Array, default:() => [] },
            tab: { type: Number, default: 0 },
            title: { type: String, default: "" },
            i18n: { type: Object, default: () => {} },
            typeOptions: { type: Object, default: () => {} },
        },

        setup(props) {
            const defaultLocale = usePage().props.value.defaultLanguage;

            const emptyTranslatedPage = getEmptyPageTranslation({
                isDefaultSettingsProvided: false
            });

            return {
                activeTab: ref(props.tab),
                contentConfigId: ref(''),
                defaultLocale,
                emptyTranslatedPage,
                iconPreview,
                localeOptions: usePage().props.value.languageOptions,
                pageEnabledOptions: { No: false, Yes: true },
                pageForm: ref(null),
                routeIndex: route(props.baseRouteName+'.index'),
                selectedLocale: ref(null),
            };
        },

        data() {
            return {
                managers: this.spaceManagers,
                productManagers: this.spaceProductManagers,
                space: {},
            };
        },

        computed: {
            isPageNew() {
                return !this.page?.id;
            },

            isPageRendered() {
                return this.spaceRecord.is_page_enabled && this.can.page.edit;
            },

            isManagerRendered() {
                return this.can.manager.edit;
            },

            isEventRendered() {
                return this.can.update;
            },

            currentTranslatedPage() {
                return getTranslation(
                    this.page,
                    this.selectedLocale,
                    { isDefaultSettingsProvided: false }
                );
            },

            pagePreviewUrl() {
                return (!isBlank(this.currentTranslatedPage.landingPageSpaceUrl)
                    ? this.currentTranslatedPage.landingPageSpaceUrl + `?&preview`
                    : null
                );
            },

            canPreviewPage() {
                return (this.currentTranslatedPage && this.pagePreviewUrl);
            },
        },

        beforeMount() {
            this.setSpace();
        },

        mounted() {
            this.changeLocale(this.defaultLocale);
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
                    'type_id',
                ]);

                space['logo'] = this.logoMedia?.id ?? null;
                space['cover'] = this.coverMedia?.id ?? null;

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
                this.selectedLocale = locale;
                this.setTranslationForm(locale);
            },

            setTranslationForm(locale) {
                let translationForm = { [this.defaultLocale]: {} };

                const translatedPage = this.currentTranslatedPage;

                if (isBlank(translatedPage)) {
                    translationForm[locale] = cloneDeep(this.emptyTranslatedPage);
                } else {
                    translationForm[locale] = cloneDeep(translatedPage);
                }

                this.pageForm = useForm(translationForm);
            },

            isUsedByMenu() {
                const self = this;

                return new Promise((resolve, reject) => {
                    const url = route('admin.api.spaces.is-used-by-menu', {
                        space: self.spaceRecord.id,
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
                    const pageTranslationStatus = this.pageForm[this.selectedLocale]['status'];

                    if (
                        this.currentTranslatedPage
                        && this.currentTranslatedPage.status == pageStatus.published
                        && pageTranslationStatus == pageStatus.draft
                    ) {
                        if (await this.isUsedByMenu()) {
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

            async submitPage() {
                if (await this.canSavePage()) {
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
                            this.pageForm[this.selectedLocale]['id'] = this.currentTranslatedPage.id;

                            successAlert(page.props.flash.message);
                        },
                        onError: () => { oopsAlert() },
                        onFinish: () => {
                            this.setTranslationForm(this.selectedLocale);
                            this.onEndLoadingOverlay();
                        }
                    };

                    this.pageForm.submit(method, url, options);
                }
            },

            previewPage() {
                window.open(this.pagePreviewUrl, "_blank");
            },
        },
    };
</script>
