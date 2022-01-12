<template>
    <div>
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
                            v-model:content-config-id="contentConfig"
                            :can="can"
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
            BizButtonLink,
            BizProvideInjectTab,
            BizProvideInjectTabs,
        },
        props: {
            can: { type: Object, required: true },
            errors: { type: Object, default:() => {} },
            isEditMode: { type: Boolean, default: true },
            isNew: { type: Boolean, required: true },
            statusOptions: { type: Array, default:() => [] },
            tabActive: { type: [Boolean, null, String], default: null },
            modelValue: { type: Object, required: true },
            localeOptions: { type: Array, default:() => [] },
            selectedLocale: { type: String, required: true },
            contentConfigId: { type: String, default: "" },
        },
        emits: ['change-locale', 'on-submit', 'update:contentConfigId'],
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
                contentConfig: useModelWrapper(props, emit, 'contentConfigId'),
            };
        },
        data() {
            return {
                disableInput: false,
            };
        }
    }
</script>
