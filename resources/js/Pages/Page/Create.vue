<template>
    <app-layout>
        <template #header>
            <h1 class="title">
                <template v-if="isNew">Create New Page</template>
                <template v-else>Update Page</template>
            </h1>
        </template>

        <div class="box mb-6">
            <form method="post" @submit.prevent="submit">
                <div class="mb-5">
                    <sdb-tabs v-model="activeTab" class="is-boxed">
                        <sdb-tab title="Detail">
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
                                :isEditMode="isEditMode"
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
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import FormBuilder from './FormBuilder';
    import FormDetail from './FormDetail';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbTab from '@/Sdb/Tab';
    import SdbTabs from '@/Sdb/Tabs';
    import { Inertia } from "@inertiajs/inertia";
    import { isBlank } from '@/Libs/utils';
    import { reactive, ref } from "vue";

    export default {
        components: {
            AppLayout,
            FormBuilder,
            FormDetail,
            SdbButton,
            SdbButtonLink,
            SdbTab,
            SdbTabs,
        },
        props: {
            page: Object,
            entityId: Number,
            errors: Object,
            statusOptions: Array,
        },
        setup(props) {
            const isNew = isBlank(props.entityId);
            let form = null;
            let submitRoute = '';

            if (isNew) {
                form = reactive({
                    title: null,
                    slug: null,
                    excerpt: null,
                    data: [],
                    meta_description: null,
                    meta_title: null,
                    status: 0,
                });
                submitRoute = route('admin.pages.store');
            } else {
                form = reactive({
                    id: props.page.id,
                    title: props.page.title,
                    slug: props.page.slug,
                    excerpt: props.page.excerpt,
                    data: props.page.data ?? [],
                    meta_description: props.page.meta_description,
                    meta_title: props.page.meta_title,
                    status: props.page.status,
                    _method: "PUT",
                });
                submitRoute = route('admin.pages.update', {id: props.page.id});
            }

            function submit() {
                Inertia.post(submitRoute, form, {});
            };

            const activeTab = ref(0);

            return {
                form, 
                submit,
                activeTab,
                isNew,
            };
        },
        data() {
            return {
                disableInput: false,
                isEditMode: true,
            };
        }
    }
</script>
