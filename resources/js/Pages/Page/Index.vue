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
                                <button class="button" @click="deleteRow(page)">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'

    export default {
        components: {
            AppLayout,
        },
        props: ['pages'],
        data() {
            return {
            };
        },
        methods: {
            deleteRow(page) {
            }
        },
    }
</script>
