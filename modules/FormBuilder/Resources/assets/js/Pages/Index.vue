<template>
    <div>
        <biz-flash-notifications :flash="$page.props.flash" />

        <div class="box">
            <div class="columns">
                <div class="column">
                    <div class="is-pulled-left">
                        <biz-filter-search
                            v-model="term"
                            @search="search"
                        />
                    </div>
                </div>

                <div class="column">
                    <div
                        v-if="can.add"
                        class="is-pulled-right"
                    >
                        <biz-button-link
                            :href="route(baseRouteName+'.create')"
                            class="is-primary"
                        >
                            <span class="icon is-small">
                                <i :class="icon.add" />
                            </span>
                            <span>Add New</span>
                        </biz-button-link>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Key</th>
                            <th>Entries</th>
                            <th>
                                <div class="level-right">
                                    Actions
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="form in records.data"
                            :key="form.id"
                        >
                            <td>{{ form.name }}</td>
                            <td>{{ form.title }}</td>
                            <td>{{ form.totalEntries }}</td>
                            <td>
                                <div class="level-right">
                                    <biz-button-link
                                        v-if="can.edit"
                                        class="is-ghost has-text-black"
                                        :href="route(baseRouteName+'.edit', {id: form.id})"
                                    >
                                        <span class="icon is-small">
                                            <i :class="icon.edit" />
                                        </span>
                                    </biz-button-link>
                                    <biz-button
                                        v-if="can.delete"
                                        class="is-ghost has-text-black ml-1"
                                        @click.prevent="deleteRow(form.id)"
                                    >
                                        <span class="icon is-small">
                                            <i :class="icon.remove" />
                                        </span>
                                    </biz-button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <biz-pagination
                :links="records.links"
                :query-params="queryParams"
            />
        </div>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizFlashNotifications from '@/Biz/FlashNotifications';
    import BizPagination from '@/Biz/Pagination';
    import icon from '@/Libs/icon-class';
    import { confirmDelete } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from 'vue';

    export default {
        name: 'FormBuilderIndex',

        components: {
            BizButton,
            BizButtonLink,
            BizFilterSearch,
            BizFlashNotifications,
            BizPagination,
        },

        mixins: [
            MixinFilterDataHandle,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            records: { type: Object, default: () => {} },
        },

        setup(props) {
            return {
                queryParams: ref(merge({},props.pageQueryParams)),
                term: ref(props.pageQueryParams?.term ?? null),
            };
        },

        data() {
            return {
                icon
            };
        },

        methods: {
            deleteRow(formId) {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', formId),
                            {
                                onStart: () => self.onStartLoadingOverlay(),
                                onFinish: () => self.onEndLoadingOverlay(),
                            }
                        );
                    }
                });
            },
        },
    };
</script>