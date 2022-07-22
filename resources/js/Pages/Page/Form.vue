<template>
    <div>
        <div
            v-if="pagePreview"
            class="columns"
        >
            <div class="column">
                <p class="buttons is-pulled-right">
                    <biz-button-icon
                        :icon="icon.preview"
                        :disabled="pagePreviewIsDisabled"
                        @click="openShow(modelValue)"
                    >
                        <span>Page Preview</span>
                    </biz-button-icon>
                </p>
            </div>
        </div>

        <div class="columns my-0">
            <div class="column py-0">
                <p class="buttons is-pulled-right">
                    <biz-button
                        v-for="locale in localeOptions"
                        :key="locale.id"
                        :class="['is-small is-link is-rounded', locale.id == selectedLocale ? '' : 'is-light' ]"
                        @click="$emit('change-locale', locale.id)"
                    >
                        {{ locale.name }}
                    </biz-button>
                </p>
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
                            :is-edit-mode="isEditMode"
                            :selected-locale="selectedLocale"
                        />
                    </biz-provide-inject-tab>
                </biz-provide-inject-tabs>
            </div>

            <slot
                name="action"
                :is-new="isNew"
            >
                <div class="field is-grouped is-grouped-right">
                    <div class="control">
                        <biz-button-link
                            class="is-link is-light"
                            :href="route('admin.pages.index')"
                        >
                            Cancel
                        </biz-button-link>
                    </div>
                    <div class="control">
                        <biz-button class="is-link">
                            {{ isNew ? 'Create' : 'Update' }}
                        </biz-button>
                    </div>
                </div>
            </slot>
        </form>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs';
    import FormBuilder from './FormBuilder';
    import FormDetail from './FormDetail';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { provide, ref } from "vue";
    import { usePage } from '@inertiajs/inertia-vue3';
    import icon from '@/Libs/icon-class';

    export default {
        components: {
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizProvideInjectTab,
            BizProvideInjectTabs,
            FormBuilder,
            FormDetail,
        },

        inject: ['can'],

        props: {
            contentConfigId: { type: String, required: true },
            errors: { type: Object, default:() => {} },
            isDirty: { type: Boolean, default: false },
            isEditMode: { type: Boolean, default: true },
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
            'change-locale',
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
            provide(
                'dataImages',
                !isBlank(images) ? images : {}
            );

            return {
                activeTab,
                form: useModelWrapper(props, emit),
                computedContentConfigId: useModelWrapper(props, emit, 'contentConfigId'),
                defaultLocale: usePage().props.value.defaultLanguage,
            };
        },

        data() {
            return {
                disableInput: false,
                icon,
            };
        },

        computed: {
            pagePreviewIsDisabled() {
                if (!this.form?.id) {
                    return true;
                }

                return this.isDirty;
            },
        },

        methods: {
            openShow(page) {
                if (
                    this.can.page.read
                    && !this.pagePreviewIsDisabled
                ) {
                    let showUrl = this.pagePreviewUrl ?? this.getShowUrl(this.selectedLocale, page);

                    window.open(showUrl, "_blank");
                }
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
            }
        },
    };
</script>
