<template>
    <app-layout>
        <template #header>
            <h2 class="">Page</h2>
        </template>

        <div class="box">
            <div class="columns">
                <div class="column">
                    <sdb-button-link :href="route('admin.pages.create')" class="is-primary">
                        Create
                    </sdb-button-link>
                </div>
            </div>

            <div class="table-container">
                <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="page in pages" :key="page.id">
                            <th>{{ page.title }}</th>
                            <td>{{ page.slug }}</td>
                            <td>
                                <sdb-button-link :href="route('admin.pages.edit', {id: page.id})">
                                    Edit
                                </sdb-button-link>
                                <sdb-button class="is-danger ml-2" @click.prevent="deleteRow(page)">
                                    Delete
                                </sdb-button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <sdb-pagination :links="records.links"></sdb-pagination>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbPagination from '@/Sdb/Pagination';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonLink,
            SdbPagination,
        },
        props: ['pages'],
        data() {
            return {
            };
        },
        methods: {
            deleteRow(page) {
                if (!confirm('Are you sure?')) return;
                this.$inertia.delete(route('admin.pages.destroy', {id: page.id}));
            }
        },
    }
</script>
