<template>
    <app-layout>
        <template #header>
            <h2 class="">Page</h2>
        </template>

        <div class="box">
            <div class="columns">
                <div class="column">
                    <inertia-link :href="route('pages.create')" class="button is-primary">
                        Create
                    </inertia-link>
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
                                <inertia-link :href="`/pages/${page.id}/edit`" class="button">
                                    Edit
                                </inertia-link>
                                <sdb-button class="is-danger" @click.prevent="deleteRow(page)">
                                    Delete
                                </sdb-button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import SdbButton from '@/Sdb/Button';

    export default {
        components: {
            AppLayout,
            SdbButton,
        },
        props: ['pages'],
        data() {
            return {
            };
        },
        methods: {
            deleteRow(page) {
                if (!confirm('Are you sure?')) return;
                this.$inertia.delete(route('pages.destroy', {id: page.id}));
            }
        },
    }
</script>
