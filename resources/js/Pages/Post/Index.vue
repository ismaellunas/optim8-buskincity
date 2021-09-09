<template>
<app-layout>
    <template #header>Post</template>

    <div class="box">
        <div class="columns">
            <div class="column is-offset-10">
                <div class="is-pulled-right">
                    <sdb-button-link
                        class="is-primary"
                        :href="route(baseRouteName+'.create')"
                    >
                        <span class="icon is-small">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span>Create New</span>
                    </sdb-button-link>
                </div>
            </div>
        </div>

        <div class="table-container">
            <sdb-table class="is-striped is-hoverable is-fullwidth">
                <tbody>
                    <tr v-for="record in records.data" :key="record.id">
                        <td>
                            <article class="media">
                                <sdb-image
                                    v-if="record.thumbnail_url"
                                    class="media-left"
                                    ratio="is-64x64"
                                    :src="record.thumbnail_url"
                                />

                                <div v-else class="media-left" style="width: 64px;"></div>

                                <div class="media-content">
                                    <div class="content">
                                        <p>
                                            <strong>{{ record.title }}</strong> {{ record.locale }}
                                            <br>
                                            {{ record.excerpt }}
                                        </p>
                                    </div>
                                </div>
                                <div class="media-right">
                                    <sdb-button-link
                                        class="is-ghost has-text-black"
                                        :href="route(baseRouteName+'.edit', {id: record.id})"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-pen"></i>
                                        </span>
                                    </sdb-button-link>
                                    <sdb-button-icon
                                        class="is-ghost has-text-black ml-1"
                                        icon="far fa-trash-alt"
                                        type="button"
                                        @click="deleteRecord(record)"
                                    />
                                </div>
                            </article>
                        </td>
                    </tr>
                </tbody>
            </sdb-table>
        </div>
        <sdb-pagination :links="records.links"></sdb-pagination>
    </div>
</app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbImage from '@/Sdb/Image';
    import SdbPagination from '@/Sdb/Pagination';
    import SdbTable from '@/Sdb/Table';
    import { confirmDelete } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            SdbButtonIcon,
            SdbButtonLink,
            SdbImage,
            SdbPagination,
            SdbTable,
        },
        props: ['records'],
        data() {
            return {
                baseRouteName: 'admin.posts',
            };
        },
        methods: {
            deleteRecord(record) {
                const self = this;
                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(route(this.baseRouteName+'.destroy', record.id));
                    }
                })
            },
        },
    };
</script>
