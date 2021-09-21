<template>
    <app-layout>
        <template #header>
            Add New Category
        </template>

        <sdb-error-notifications :errors="$page.props.errors"/>

        <div class="box mb-6">
            <category-form
                :base-route="baseRoute"
                :default-locale="defaultLocale"
                :errors="errors"
                :is-edit-mode="isEditMode"
                :is-input-disabled="isProcessing"
                :is-new="isNew"
                :locale-options="localeOptions"
                @on-submit="submit"
            />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import CategoryForm from '@/Pages/Category/Form';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            CategoryForm,
            SdbErrorNotifications,
        },
        props: {
            baseRoute: String,
            defaultLocale: String,
            errors: Object,
            localeOptions: Array,
        },
        data() {
            return {
                isEditMode: true,
                isNew: true,
                isProcessing: false,
                loader: null,
            };
        },
        methods: {
            submit(form) {
                const self = this;

                this.$inertia.post(route(this.baseRoute + '.store'), form, {
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;
                    },
                });
            },
        },
    };
</script>
