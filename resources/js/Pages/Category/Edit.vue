<template>
    <app-layout>
        <template #header>Update Category</template>

        <sdb-error-notifications :errors="$page.props.errors"/>

        <sdb-flash-notifications :flash="$page.props.flash"/>

        <div class="box mb-6">
            <category-form
                :category="record"
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
    import SdbFlashNotifications from '@/Sdb/FlashNotifications';
    import { Inertia } from '@inertiajs/inertia';
    import { reactive } from 'vue';

    export default {
        components: {
            AppLayout,
            CategoryForm,
            SdbErrorNotifications,
            SdbFlashNotifications,
        },
        props: {
            baseRoute: String,
            defaultLocale: String,
            errors: Object,
            localeOptions: Array,
            record: Object,
        },
        setup(props) {
            function submit(form) {
                Inertia.put(
                    route(props.baseRoute + '.update', props.record.id),
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
                isNew: false,
            };
        }
    }
</script>
