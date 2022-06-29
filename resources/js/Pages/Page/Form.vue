<template>
    <div>
        <div
            v-if="pagePreview"
            class="columns"
        >
            <div class="column">
                <p class="buttons is-pulled-right">
                    <biz-button-icon
                        icon="fa-solid fa-arrow-up-right-from-square"
                        :disabled="isDirty"
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
                <biz-provide-inject-tabs v-model="activeTab" class="is-boxed">
                    <biz-provide-inject-tab title="Details">
                        <form-detail
                            v-model:title="form.title"
                            v-model:slug="form.slug"
                            v-model:excerpt="form.excerpt"
                            v-model:meta_description="form.meta_description"
                            v-model:meta_title="form.meta_title"
                            v-model:status="form.status"
                            :disable-input="disableInput"
                            :errors="errors"
                            :status-options="statusOptions"
                            :selected-locale="selectedLocale"
                        />
                    </biz-provide-inject-tab>
                    <biz-provide-inject-tab title="Builder">
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

            <div class="field is-grouped is-grouped-right">
                <div class="control">
                    <biz-button-link
                        :href="route('admin.pages.index')"
                        class="is-link is-light">
                        Cancel
                    </biz-button-link>
                </div>
                <div class="control">
                    <biz-button class="is-link">
                        <template v-if="isNew">
                            Create
                        </template>
                        <template v-else>
                            Update
                        </template>
                    </biz-button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import FormBuilder from './FormBuilder';
    import FormDetail from './FormDetail';
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { provide, ref } from "vue";
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            FormBuilder,
            FormDetail,
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizProvideInjectTab,
            BizProvideInjectTabs,
        },

        inject: ['can'],

        props: {
            contentConfigId: { type: String, required: true },
            errors: { type: Object, default:() => {} },
            isDirty: { type: Boolean, default: false },
            isEditMode: { type: Boolean, default: true },
            isNew: { type: Boolean, required: true },
            localeOptions: { type: Array, default:() => [] },
            modelValue: { type: Object, required: true },
            pagePreview: { type: Boolean, default: false },
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
            };
        },

        methods: {
            openShow(page) {
                if (
                    this.can.page.read
                    && !this.isDirty
                ) {
                    let showUrl = this.getShowUrl(this.selectedLocale, page);

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
