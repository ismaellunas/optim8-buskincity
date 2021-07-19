<template>
    <form method="post" @submit.prevent="submit">
        <div class="mb-5">
            <sdb-tabs v-model="activeTab" class="is-boxed">
                <sdb-tab title="Details">
                    <form-detail
                        v-model:title="form.title"
                        v-model:slug="form.slug"
                        v-model:excerpt="form.excerpt"
                        v-model:meta_description="form.meta_description"
                        v-model:meta_title="form.meta_title"
                        v-model:status="form.status"
                        :errors="errors"
                        :disableInput="disableInput"
                        :statusOptions="statusOptions"
                        />
                </sdb-tab>
                <sdb-tab title="Builder">
                    <form-builder
                        v-model="form.data"
                        :isEditMode="isEditMode && !isNew"
                        />
                </sdb-tab>
            </sdb-tabs>
        </div>

        <div class="field is-grouped is-grouped-right">
            <div class="control">
                <sdb-button-link :href="route('admin.pages.index')" class="is-link is-light">
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
    import SdbTab from '@/Sdb/Tab';
    import SdbTabs from '@/Sdb/Tabs';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { ref, reactive } from "vue";
    import { Inertia } from '@inertiajs/inertia';

    export default {
        components: {
            FormBuilder,
            FormDetail,
            SdbButton,
            SdbButtonLink,
            SdbTab,
            SdbTabs,
        },
        props: {
            errors: {},
            isEditMode: Boolean,
            isNew: Boolean,
            statusOptions: Array,
            submit: Function,
            tabActive: {},
            modelValue: {},
        },
        setup(props, { emit }) {
            let activeTab = null;

            if (!isBlank(props.tabActive) && props.tabActive === 'builder') {
                activeTab = ref(1);
            } else {
                activeTab = ref(1);
            }

            return {
                activeTab,
                form: useModelWrapper(props, emit),
            };
        },
        data() {
            return {
                disableInput: false,
            };
        },
    }
</script>
