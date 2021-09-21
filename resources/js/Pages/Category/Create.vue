<template>
    <app-layout>
        <template #header>
            Add New Category
        </template>

        <sdb-error-notifications :errors="$page.props.errors"/>

        <div class="box mb-6">
            <category-form
                :errors="errors"
                :isNew="isNew"
                :isEditMode="isEditMode"
                :submit="submit"
                :localeOptions="localeOptions"
                :defaultLocale="defaultLocale"
                :baseRoute="baseRoute"
            />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import CategoryForm from '@/Pages/Category/Form';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';

    export default {
        components: {
            AppLayout,
            CategoryForm,
            SdbErrorNotifications,
        },
        props: {
            baseRoute: String,
            errors: Object,
            localeOptions: Array,
            defaultLocale: String,
        },
        setup(props) {
            function submit(form) {
                Inertia.post(
                    route(props.baseRoute + '.store'),
                    form
                );
            };

            return {
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
