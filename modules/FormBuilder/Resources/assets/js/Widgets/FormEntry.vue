<template>
    <div class="column is-6">
        <biz-panel class="is-white is-relative">
            <template #heading>
                <div class="columns">
                    <div class="column">
                        {{ title }}
                    </div>
                    <div
                        v-if="hasFormLists"
                        class="column"
                    >
                        <biz-select
                            v-model="selectedForm"
                            class="is-small is-pulled-right"
                            @change="onSelectedFormChange()"
                        >
                            <option
                                v-for="(value, key, index) in data.formLists"
                                :key="index"
                                :value="key"
                            >
                                {{ value }}
                            </option>
                        </biz-select>
                    </div>
                </div>
            </template>

            <template #default>
                <biz-loader
                    v-model="isLoading"
                    :is-full-page="false"
                />

                <biz-panel-block>
                    <div
                        class="table-container"
                        style="width: 100%"
                    >
                        <biz-table is-fullwidth>
                            <thead>
                                <tr>
                                    <th
                                        v-for="(label, index) in fieldLabels"
                                        :key="index"
                                    >
                                        {{ label }}
                                    </th>
                                    <th />
                                </tr>
                            </thead>
                            <tbody>
                                <template v-if="hasRecords">
                                    <tr
                                        v-for="(entry, index) in records.data"
                                        :key="index"
                                    >
                                        <td
                                            v-for="(name, nameIndex) in fieldNames"
                                            :key="nameIndex"
                                            class="content"
                                            v-html="entry[name]"
                                        />
                                        <td>
                                            <biz-button-link
                                                class="is-primary is-outlined is-small"
                                                :href="route(data.baseRouteName + '.entries.show', {form_builder: selectedForm, form_entry: entry.id})"
                                            >
                                                {{ i18n.view_detail }}
                                            </biz-button-link>
                                        </td>
                                    </tr>
                                </template>

                                <template
                                    v-else
                                >
                                    <tr>
                                        <td
                                            class="has-text-centered"
                                            :colspan="fieldLabels.length + 1"
                                        >
                                            {{ i18n.no_data }}
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </biz-table>
                    </div>
                </biz-panel-block>

                <biz-panel-block
                    v-if="records?.links?.length > 3"
                >
                    <biz-pagination
                        :links="records?.links"
                        :is-ajax="true"
                        @on-clicked-pagination="getRecords"
                    />
                </biz-panel-block>

                <biz-panel-block>
                    <div
                        class="level"
                        style="width: 100%"
                    >
                        <div class="level-left" />
                        <div class="level-right">
                            <biz-button-link
                                v-if="!!selectedForm"
                                class="is-primary is-outlined is-small"
                                :href="route(data.baseRouteName + '.entries.index', selectedForm)"
                            >
                                {{ i18n.view_all }}
                            </biz-button-link>
                        </div>
                    </div>
                </biz-panel-block>
            </template>
        </biz-panel>
    </div>
</template>

<script>
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizLoader from '@/Biz/Loader.vue';
    import BizPagination from '@/Biz/Pagination.vue';
    import BizPanel from '@/Biz/Panel.vue';
    import BizPanelBlock from '@/Biz/PanelBlock.vue';
    import BizSelect from '@/Biz/Select.vue';
    import BizTable from '@/Biz/Table.vue';
    import icon from '@/Libs/icon-class';
    import { head, isEmpty } from 'lodash';

    export default {
        name: 'FormBuilderEntryWidget',

        components: {
            BizButtonLink,
            BizLoader,
            BizPagination,
            BizPanel,
            BizPanelBlock,
            BizSelect,
            BizTable,
        },

        props: {
            data: { type: Object, required: true },
            i18n: { type: Object, default: () => ({
                view_detail : 'View Detail',
                view_all : 'View All',
                no_data : 'No Data',
            }) },
            title: { type: String, default: "" },
        },

        data() {
            return {
                fieldLabels: [],
                fieldNames: [],
                icon,
                isLoading: false,
                records: [],
                selectedForm: null,
            };
        },

        computed: {
            hasRecords() {
                return this.records?.data?.length > 0;
            },

            hasFormLists() {
                return !isEmpty(this.data.formLists);
            }
        },

        mounted() {
            if (this.hasFormLists) {
                this.selectedForm = head(Object.keys(this.data.formLists));

                this.getRecords();
            }
        },

        methods: {
            getRecords(url = null) {
                const self = this;

                url = url ? url : route('admin.api.widget.form-builder.entries', self.selectedForm);

                self.isLoading = true,

                axios.get(url)
                    .then((response) => {
                        self.fieldLabels = response.data.fieldLabels;
                        self.fieldNames = response.data.fieldNames;
                        self.records = response.data.records;
                    })
                    .then(() => {
                        self.isLoading = false;
                    });
            },

            onSelectedFormChange() {
                this.getRecords();
            },
        }
    }
</script>