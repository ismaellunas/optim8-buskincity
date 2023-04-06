<template>
    <div>
        <biz-flash-notifications :flash="$page.props.flash" />

        <div class="box">
            <div class="columns">
                <div class="column is-4">
                    <biz-filter-search
                        v-model="term"
                        :placeholder="i18n.search"
                        @search="search"
                    />
                </div>

                <div class="column has-text-right">
                    <biz-button-link
                        v-if="can.add"
                        :href="route(baseRouteName+'.create')"
                        class="is-primary"
                    >
                        <span class="icon is-small">
                            <i :class="icon.add" />
                        </span>
                        <span>{{ i18n.create_new }}</span>
                    </biz-button-link>
                </div>
            </div>

            <biz-table-index
                :records="records"
                :query-params="queryParams"
            >
                <template #thead>
                    <tr>
                        <th>{{ i18n.name }}</th>
                        <th>{{ i18n.form_id }}</th>
                        <th>{{ i18n.entries }}</th>
                        <th>
                            <div class="level-right">
                                {{ i18n.actions }}
                            </div>
                        </th>
                    </tr>
                </template>

                <tr
                    v-for="form in records.data"
                    :key="form.id"
                >
                    <td>{{ form.name }}</td>
                    <td>{{ form.key }}</td>
                    <td>
                        <biz-link
                            :href="route(baseRouteName + '.entries.index', {form_builder: form.id})"
                            title="List Entries"
                        >
                            {{ form.totalEntries }}
                        </biz-link>
                    </td>
                    <td>
                        <div class="level-right">
                            <biz-button-link
                                v-if="can.browse"
                                class="is-ghost has-text-black"
                                title="List Entries"
                                :href="route(baseRouteName + '.entries.index', {form_builder: form.id})"
                            >
                                <span class="icon is-small">
                                    <i :class="icon.rectangleList" />
                                </span>
                            </biz-button-link>
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
            </biz-table-index>
        </div>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizFlashNotifications from '@/Biz/FlashNotifications.vue';
    import BizLink from '@/Biz/Link.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
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
            BizLink,
            BizTableIndex,
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
            i18n: { type: Object, default: () => ({
                search : 'search',
                create_new : 'Create New',
                name : 'Name',
                form_id : 'Form ID',
                entries : 'Entries',
                actions : 'Actions',
                are_you_sure : 'Are you sure?',
            }) },
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

                confirmDelete(
                    self.i18n.are_you_sure
                ).then((result) => {
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