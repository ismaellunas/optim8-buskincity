<template>
    <app-layout>
        <template #header>Category</template>

        <sdb-flash-notifications :flash="$page.props.flash"/>

        <div class="box">
            <div class="columns">
                <div class="column is-offset-10">
                    <div class="is-pulled-right">
                        <sdb-button-link :href="route(baseRoute+'.create')" class="is-primary">
                            <span class="icon is-small">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span>Create New</span>
                        </sdb-button-link>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>
                                <div class="level-right">Actions</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in records.data" :key="record.id">
                            <th>{{ record.id }}</th>
                            <th>{{ record.name }}</th>
                            <td>
                                <div class="level-right">
                                    <sdb-button-link
                                        class="is-ghost has-text-black"
                                        :href="route(baseRoute + '.edit', record.id)"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-pen"></i>
                                        </span>
                                    </sdb-button-link>
                                    <sdb-button
                                        class="is-ghost has-text-black ml-1"
                                        @click.prevent="deleteRow(record)"
                                    >
                                        <span class="icon is-small">
                                            <i class="far fa-trash-alt"></i>
                                        </span>
                                    </sdb-button>
                                </div>
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
    import SdbFlashNotifications from '@/Sdb/FlashNotifications';
    import SdbPagination from '@/Sdb/Pagination';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonLink,
            SdbFlashNotifications,
            SdbPagination,
        },
        props: ['records'],
        data() {
            return {
                baseRoute: 'admin.categories',
            };
        },
        methods: {
            deleteRow(record) {
                if (!confirm('Are you sure?')) return;
                this.$inertia.delete(route(this.baseRoute+'.destroy', record.id));
            },
        },
        computed: {
        }
    }
</script>
