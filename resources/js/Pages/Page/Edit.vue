<template>
    <app-layout>
        <template #header>
            Update Page
        </template>

        <sdb-error-notifications :errors="$page.props.errors"/>

        <sdb-flash-notifications :flash="$page.props.flash"/>

        <div class="box mb-6">
            <page-form
                :form="form"
                :errors="errors"
                :isNew="isNew"
                :isEditMode="isEditMode"
                :statusOptions="statusOptions"
                :submit="submit"
                :tabActive="tabActive"
            />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import PageForm from '@/Pages/Page/Form';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbFlashNotifications from '@/Sdb/FlashNotifications';
    import { Inertia } from "@inertiajs/inertia";
    import { isBlank } from '@/Libs/utils';
    import { reactive, ref } from "vue";

    export default {
        components: {
            AppLayout,
            PageForm,
            SdbErrorNotifications,
            SdbFlashNotifications,
        },
        props: {
            page: Object,
            entityId: Number,
            errors: Object,
            statusOptions: Array,
            tabActive: String,
        },
        setup(props) {
            const form = reactive({
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

            let submitRoute = route('admin.pages.update', {id: props.page.id});

            function submit() {
                Inertia.put(
                    submitRoute,
                    form,
                    {}
                );
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
                isNew: false,
            };
        }
    }
</script>
