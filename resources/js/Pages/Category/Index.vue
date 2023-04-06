<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column is-4">
                    <biz-filter-search
                        v-model="term"
                        :placeholder="i18n.search"
                        @search="search"
                    />
                </div>

                <div class="column is-4 is-offset-4 has-text-right">
                    <biz-button-link
                        v-if="can.add"
                        class="is-primary"
                        :href="route(baseRouteName+'.create')"
                    >
                        <biz-icon :icon="iconAdd" />
                        <span>
                            {{ i18n.create_new }}
                        </span>
                    </biz-button-link>
                </div>
            </div>

            <biz-table-index
                :records="records"
                :query-params="queryParams"
            >
                <template #thead>
                    <tr>
                        <th>#</th>
                        <th
                            v-for="locale in localeOptions"
                            :key="locale.id"
                        >
                            {{ locale.name }}
                        </th>
                        <th>
                            <div class="level-right">
                                {{ i18n.actions }}
                            </div>
                        </th>
                    </tr>
                </template>

                <tr
                    v-for="record in records.data"
                    :key="record.id"
                >
                    <th>{{ record.id }}</th>
                    <td
                        v-for="locale in localeOptions"
                        :key="locale.id"
                    >
                        {{ getNameByLocale(record, locale.id) }}
                    </td>
                    <td>
                        <div class="level-right">
                            <biz-button-link
                                v-if="can.edit"
                                class="is-ghost has-text-black"
                                :href="route(baseRouteName + '.edit', record.id)"
                            >
                                <biz-icon :icon="iconEdit" />
                            </biz-button-link>
                            <biz-button-icon
                                v-if="can.delete"
                                class="is-ghost has-text-black ml-1"
                                :icon="iconRemove"
                                @click.prevent="deleteRow(record)"
                            />
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
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import { add as iconAdd, edit as iconEdit, remove as iconRemove } from '@/Libs/icon-class';
    import { confirmDelete, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from 'vue';
    import { usePage } from '@inertiajs/vue3';

    export default {
        name: 'CategoryIndex',

        components: {
            BizButtonIcon,
            BizButtonLink,
            BizFilterSearch,
            BizIcon,
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
            records: { type: Object, required: true },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => ({
                search : 'Search',
                actions : 'Actions',
                create_new : 'Create New',
                are_you_sure : 'Are you sure?',
            }) }
        },

        setup(props) {
            return {
                defaultLocale: usePage().props.defaultLanguage,
                localeOptions: usePage().props.languageOptions,
                queryParams: ref(merge({},props.pageQueryParams)),
                term: ref(props.pageQueryParams?.term ?? null),
                loader: ref(null),
                iconAdd,
                iconEdit,
                iconRemove,
            };
        },

        methods: {
            deleteRow(record) {
                const self = this;

                confirmDelete(self.i18n.are_you_sure).then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', record.id),
                            {
                                onStart: () => this.onStartLoadingOverlay(),
                                onFinish: () => this.onEndLoadingOverlay(),
                                onSuccess: self.onSuccess,
                            }
                        );
                    }
                });
            },

            onSuccess(page) {
                successAlert(page.props.flash.message);
            },

            getNameByLocale(record, locale) {
                const translation = record.translations.find(translation => translation.locale === locale);
                if (translation) {
                    return translation.name;
                }
                return "";
            },
        },
    };
</script>
