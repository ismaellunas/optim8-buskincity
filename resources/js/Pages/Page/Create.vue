<template>
    <app-layout>
        <template #header>
            Create New Page
        </template>

        <sdb-error-notifications :errors="$page.props.errors"/>

        <div class="box mb-6">
            <page-form
                v-model="form"
                :errors="errors"
                :isNew="isNew"
                :isEditMode="isEditMode"
                :statusOptions="statusOptions"
                :submit="submit"
            />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import PageForm from '@/Pages/Page/Form';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import { Inertia } from "@inertiajs/inertia";
    import { isBlank } from '@/Libs/utils';
    import { reactive, ref } from "vue";

    export default {
        components: {
            AppLayout,
            PageForm,
            SdbErrorNotifications,
        },
        props: {
            errors: Object,
            statusOptions: Array,
            tabActive: String,
        },
        setup(props) {
            const form = reactive({
                title: null,
                slug: null,
                excerpt: null,
                data: [],
                meta_description: null,
                meta_title: null,
                status: 0,
            });

            const submitRoute = route('admin.pages.store');

            function submit() {
                Inertia.post(submitRoute, form, {});
            };

            return {
                form, 
                submit,
            };
        },
        data() {
            return {
                disableInput: false,
                isEditMode: true,
                isNew: true,
            };
        }
    }
</script>
