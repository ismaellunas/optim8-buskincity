<template>
    <div>
        <div
            v-if="pagePreview"
            class="columns"
        >
            <div class="column">
                <p class="buttons is-right">
                    <biz-button-icon
                        :icon="icon.preview"
                        :disabled="!canPreview"
                        @click="openShow(modelValue)"
                    >
                        <span>Page Preview</span>
                    </biz-button-icon>
                </p>
            </div>
        </div>

        <div class="columns my-0">
            <div class="column py-0">
                <biz-language-tab
                    class="is-right"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @on-change-locale="onChangeLocale"
                />
            </div>
        </div>

        <form
            method="post"
            @submit.prevent="$emit('on-submit')"
        >
            <div class="mb-5">
                <biz-provide-inject-tabs
                    v-model="activeTab"
                    class="is-boxed"
                >
                    <biz-provide-inject-tab title="Details">
                        <form-detail
                            v-model:title="form.title"
                            v-model:slug="form.slug"
                            v-model:excerpt="form.excerpt"
                            v-model:meta-description="form.meta_description"
                            v-model:meta-title="form.meta_title"
                            v-model:status="form.status"
                            :disable-input="disableInput"
                            :errors="errors"
                            :selected-locale="selectedLocale"
                            :status-options="statusOptions"
                        />
                    </biz-provide-inject-tab>
                    <biz-provide-inject-tab
                        title="Builder"
                        :is-rendered="isPageBuilderRendered"
                    >
                        <form-builder
                            id="page-form-builder"
                            v-model="form.data"
                            v-model:content-config-id="computedContentConfigId"
                            :selected-locale="selectedLocale"
                        />
                    </biz-provide-inject-tab>
                    <biz-provide-inject-tab title="Settings">
                        <form-setting
                            v-model="form.settings"
                            :errors="errors"
                            :selected-locale="selectedLocale"
                        />
                    </biz-provide-inject-tab>
                </biz-provide-inject-tabs>
            </div>

            <slot
                name="action"
                :is-new="isNew"
            >
                <div class="columns">
                    <div class="column">
                        <div class="buttons">
                            <biz-button
                                v-if="duplicateTranslationIsShowed"
                                type="button"
                                class="is-link"
                                @click="openModal()"
                            >
                                Duplicate
                            </biz-button>

                            <biz-button
                                v-if="deleteTranslationIsShowed"
                                type="button"
                                class="is-danger"
                                @click="$emit('on-delete-translation')"
                            >
                                Remove
                            </biz-button>
                        </div>
                    </div>

                    <div class="column">
                        <div class="buttons is-pulled-right">
                            <biz-button-link
                                class="is-link is-light"
                                :href="route('admin.pages.index')"
                            >
                                Cancel
                            </biz-button-link>

                            <biz-button class="is-link">
                                {{ isNew ? 'Create' : 'Update' }}
                            </biz-button>
                        </div>
                    </div>
                </div>
            </slot>
        </form>

        <biz-modal-card
            v-if="isModalOpen"
            @close="closeModal()"
        >
            <template #header>
                <p class="modal-card-title has-text-weight-bold">
                    Duplicate Translation
                </p>

                <button
                    aria-label="close"
                    class="delete"
                    @click="closeModal()"
                />
            </template>

            <biz-form-select
                v-model="formDuplicate.to"
                label="To"
                :is-fullwidth="true"
            >
                <option :value="null">
                    - Select Translation -
                </option>
                <option
                    v-for="locale in emptyPageLocaleOptions"
                    :key="locale.id"
                    :value="locale.id"
                >
                    {{ locale.name }}
                </option>
            </biz-form-select>

            <template #footer>
                <div
                    class="columns mx-0"
                    style="width: 100%"
                >
                    <div class="column px-0">
                        <div class="is-pulled-right">
                            <biz-button @click="closeModal()">
                                Cancel
                            </biz-button>
                            <biz-button
                                class="is-link"
                                :disabled="!formDuplicate.to"
                                @click="onDuplicateTranslation()"
                            >
                                Duplicate
                            </biz-button>
                        </div>
                    </div>
                </div>
            </template>
        </biz-modal-card>
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizLanguageTab from '@/Biz/LanguageTab';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizModalCard from '@/Biz/ModalCard';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs';
    import FormBuilder from './FormBuilder';
    import FormDetail from './FormDetail';
    import FormSetting from './FormSetting';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { ref } from "vue";
    import { usePage } from '@inertiajs/inertia-vue3';
    import { cloneDeep } from 'lodash';
    import icon from '@/Libs/icon-class';

    export default {
        components: {
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizLanguageTab,
            BizFormSelect,
            BizModalCard,
            BizProvideInjectTab,
            BizProvideInjectTabs,
            FormBuilder,
            FormDetail,
            FormSetting,
        },

        mixins: [
            MixinHasModal,
        ],

        inject: ['can'],

        provide() {
            return {
                dataImages: this.images,
            }
        },

        props: {
            contentConfigId: { type: String, required: true },
            emptyPageLocaleOptions: { type: Array, default: () => [] },
            errors: { type: Object, default:() => {} },
            isDirty: { type: Boolean, default: false },
            isNew: { type: Boolean, required: true },
            isPageBuilderRendered: { type: Boolean, default: true },
            localeOptions: { type: Array, default:() => [] },
            modelValue: { type: Object, required: true },
            pagePreview: { type: Boolean, default: false },
            pagePreviewUrl: { type: String, default: null },
            selectedLocale: { type: String, required: true },
            statusOptions: { type: Array, default:() => [] },
            tabActive: { type: [Boolean, null, String], default: null },
        },

        emits: [
            'on-change-locale',
            'on-delete-translation',
            'on-duplicate-translation',
            'on-submit',
            'update:contentConfigId',
        ],

        setup(props, { emit }) {
            let activeTab = null;

            if (!isBlank(props.tabActive) && props.tabActive === 'builder') {
                activeTab = ref(1);
            } else {
                activeTab = ref(0);
            }

            // Set provide and inject of images data
            const images = usePage().props.value.images;

            return {
                activeTab,
                form: useModelWrapper(props, emit),
                computedContentConfigId: useModelWrapper(props, emit, 'contentConfigId'),
                defaultLocale: usePage().props.value.defaultLanguage,
                images: !isBlank(images) ? images : {},
            };
        },

        data() {
            return {
                disableInput: false,
                icon,
                formDuplicate: {
                    to: null,
                },
            };
        },

        computed: {
            canPreview() {
                return (
                    this.can.page.read
                    && this.form.id
                    && !this.isDirty
                )
            },

            deleteTranslationIsShowed() {
                return (this.defaultLocale !== this.selectedLocale)
                    && this.form?.id
                    && this.can?.page?.delete;
            },

            duplicateTranslationIsShowed() {
                return !!this.form?.id;
            },
        },

        methods: {
            openShow(page) {
                let showUrl = this.pagePreviewUrl ?? this.getShowUrl(this.selectedLocale, page);

                window.open(showUrl, "_blank");
            },

            getShowUrl(locale, page) {
                let showUrl = null;
                let defaultUrl = route('frontend.pages.show', {
                    page_translation: page.slug
                });
                let url = new URL(defaultUrl);

                if (locale !== this.defaultLocale) {
                    showUrl = defaultUrl.replace(url.pathname, "/"+locale+url.pathname);
                } else {
                    showUrl = defaultUrl;
                }

                return showUrl;
            },

            onChangeLocale(localeId) {
                this.$emit('on-change-locale', localeId);
            },

            onDuplicateTranslation() {
                if (!!this.formDuplicate.to) {
                    this.images[this.formDuplicate.to] = cloneDeep(
                        this.images[this.selectedLocale]
                    );

                    this.$emit('on-duplicate-translation', {
                        locale: this.formDuplicate.to,
                        form: {
                            id: null,
                            title: this.form.title,
                            slug: this.form.slug,
                            excerpt: this.form.excerpt,
                            data: this.form.data,
                            meta_description: this.form.meta_description,
                            meta_title: this.form.meta_title,
                            status: 0,
                            settings: this.form.settings,
                        },
                    });

                    this.closeModal();

                    this.formDuplicate.to = null;
                }
            },

            onCloseModal() {
                this.formDuplicate.to = null;
            },
        },
    };
</script>
