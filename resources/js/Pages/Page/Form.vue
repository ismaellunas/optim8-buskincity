<template>
    <div class="columns my-0">
        <div class="column py-0">
            <p class="buttons is-pulled-right">
                <sdb-button
                    v-for="locale in localeOptions"
                    @click="$emit('change-locale', locale.id)"
                    :class="['is-small is-link is-rounded', locale.id == selectedLocale ? '' : 'is-light' ]"
                    >
                    {{ locale.name }}
                </sdb-button>
            </p>
        </div>
    </div>

    <form method="post" @submit.prevent="$emit('on-submit')">
        <div class="mb-5">
            <sdb-provide-inject-tabs v-model="activeTab" class="is-boxed">
                <sdb-provide-inject-tab title="Details">
                    <form-detail
                        v-model:title="form.title"
                        v-model:slug="form.slug"
                        v-model:excerpt="form.excerpt"
                        v-model:meta_description="form.meta_description"
                        v-model:meta_title="form.meta_title"
                        v-model:status="form.status"
                        :disableInput="disableInput"
                        :errors="errors"
                        :statusOptions="statusOptions"
                        :selected-locale="selectedLocale"
                    />
                </sdb-provide-inject-tab>
                <sdb-provide-inject-tab title="Builder">
                    <form-builder
                        id="page-form-builder"
                        v-model="form.data"
                        v-model:content-config-id="contentConfigId"
                        :can="can"
                        :is-edit-mode="isEditMode"
                        :selected-locale="selectedLocale"
                    />
                </sdb-provide-inject-tab>
            </sdb-provide-inject-tabs>
        </div>

        <div class="field is-grouped is-grouped-right">
            <div class="control">
                <sdb-button-link
                    :href="route('admin.pages.index')"
                    class="is-link is-light">
                    Cancel
                </sdb-button-link>
            </div>
            <div class="control">
                <sdb-button class="is-link">
                    <template v-if="isNew">Create</template>
                    <template v-else>Update</template>
                </sdb-button>
            </div>
        </div>
    </form>
</template>

<script>
    import FormBuilder from './FormBuilder';
    import FormDetail from './FormDetail';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbProvideInjectTab from '@/Sdb/ProvideInjectTab/Tab';
    import SdbProvideInjectTabs from '@/Sdb/ProvideInjectTab/Tabs';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { ref } from "vue";

    export default {
        components: {
            FormBuilder,
            FormDetail,
            SdbButton,
            SdbButtonLink,
            SdbProvideInjectTab,
            SdbProvideInjectTabs,
        },
        emits: ['change-locale', 'on-submit', 'update:contentConfigId'],
        props: {
            can: Object,
            errors: {},
            isEditMode: Boolean,
            isNew: Boolean,
            statusOptions: Array,
            tabActive: {},
            modelValue: {},
            localeOptions: Array,
            selectedLocale: String,
            contentConfigId: {},
        },
        setup(props, { emit }) {
            let activeTab = null;

            if (!isBlank(props.tabActive) && props.tabActive === 'builder') {
                activeTab = ref(1);
            } else {
                activeTab = ref(0);
            }

            return {
                activeTab,
                form: useModelWrapper(props, emit),
                contentConfigId: useModelWrapper(props, emit, 'contentConfigId'),
            };
        },
        data() {
            return {
                disableInput: false,
            };
        },
    }
</script>
