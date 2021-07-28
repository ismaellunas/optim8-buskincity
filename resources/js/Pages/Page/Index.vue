<template>
    <app-layout>
        <template #header>Pages</template>

        <sdb-flash-notifications :flash="$page.props.flash"/>

        <div class="box">
            <div class="columns">
                <div class="column is-offset-10">
                    <div class="is-pulled-right">
                        <sdb-button-link :href="route('admin.pages.create')" class="is-primary">
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
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>M.Title</th>
                            <th>M.Description</th>
                            <th>Language</th>
                            <th>
                                <div class="level-right">Actions</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="page in records.data" :key="page.id">
                            <th>{{ page.id }}</th>
                            <td>{{ page.title }}</td>
                            <td>{{ page.slug }}</td>
                            <td>
                                <sdb-tag :class="statusClass(page.status)">
                                    {{ page.statusText }}
                                </sdb-tag>
                            </td>
                            <td><i class="far fa-check-circle" v-if="page.hasMetaTitle"></i></td>
                            <td><i class="far fa-check-circle" v-if="page.hasMetaDescription"></i></td>
                            <td>
                                <sdb-button
                                    v-for="translation in page.translations"
                                    @click="openShow(translation.locale, page)"
                                    class="is-info px-2 mr-1 is-small">
                                    {{ translation.locale?.toUpperCase() }}
                                </sdb-button>
                            </td>
                            <td>
                                <div class="level-right">
                                    <sdb-button-link
                                        class="is-ghost has-text-black"
                                        :href="route('admin.pages.edit', {id: page.id})"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-pen"></i>
                                        </span>
                                    </sdb-button-link>
                                    <sdb-button
                                        class="is-ghost has-text-black ml-1"
                                        @click.prevent="deleteRow(page)"
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
    import SdbTag from '@/Sdb/Tag';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonLink,
            SdbFlashNotifications,
            SdbPagination,
            SdbTag,
        },
        props: ['records', 'defaultLocale'],
        methods: {
            deleteRow(page) {
                if (!confirm('Are you sure?')) return;
                this.$inertia.delete(route('admin.pages.destroy', {id: page.id}));
            },
            openShow(locale, page) {
                window.open(this.route('pages.show', [locale, page.slug]), "_blank");
            },
            statusClass(status) {
                let statusClass = ['is-small', 'is-rounded'];
                switch(status) {
                    case 1 : statusClass.push('is-success'); break;
                    default: statusClass.push('is-light');
                };
                return statusClass;
            }
        },
    }
</script>
