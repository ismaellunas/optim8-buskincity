<template>
<app-layout>
    <template #header>
        Media
    </template>

    <div class="box">
        <div class="columns">
            <div class="column">
                <sdb-button-link :href="route(baseRouteName+'.create')" class="is-primary">
                    Upload
                </sdb-button-link>
            </div>
        </div>

        <div class="table-container">
            <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Size (bytes)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="record in records.data" :key="record.id">
                        <th>
                            <figure
                                class="image is-96x96"
                                v-html="record.tag_url">
                            </figure>
                        </th>
                        <td>{{ record.size }}</td>
                        <td>
                            <sdb-button class="is-danger ml-2" @click.prevent="deleteRecord(record)">
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
    import SdbButtonLink from '@/Sdb/ButtonLink';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonLink,
        },
        props: ['records', 'baseRouteName'],
        methods: {
            getEditRoute(id) {
                // return this.baseRouteName.replace('.', '/')+'/'+id+'/edit';
                return route(this.baseRouteName+'.edit', {id});
            },
            deleteRecord(record) {
                if (!confirm('Are you sure?')) return;
                this.$inertia.delete(route(this.baseRouteName+'.destroy', {id: record.id}));
            }
        },
        computed: {
        }
    }
</script>
