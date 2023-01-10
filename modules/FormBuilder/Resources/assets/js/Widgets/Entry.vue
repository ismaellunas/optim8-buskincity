<template>
    <div class="column is-6">
        <biz-panel class="is-white">
            <template #heading>
                <div class="columns">
                    <div class="column">
                        {{ title }}
                    </div>
                    <div class="column">
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
                                                :href="route(data.baseRouteName + '.entries.show', {form_builder: selectedForm, entry: entry.id})"
                                            >
                                                View Detail
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
                                            Empty
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
                                :href="route(data.baseRouteName + '.entries', selectedForm)"
                            >
                                View All
                            </biz-button-link>
                        </div>
                    </div>
                </biz-panel-block>
            </template>
        </biz-panel>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizPagination from '@/Biz/Pagination';
    import BizPanel from '@/Biz/Panel';
    import BizPanelBlock from '@/Biz/PanelBlock';
    import BizSelect from '@/Biz/Select';
    import BizTable from '@/Biz/Table';
    import icon from '@/Libs/icon-class';
    import { head } from 'lodash';

    export default {
        name: 'FormBuilderEntryWidget',

        components: {
            BizButton,
            BizButtonLink,
            BizPagination,
            BizPanel,
            BizPanelBlock,
            BizSelect,
            BizTable,
        },

        props: {
            data: { type: Object, required: true },
            title: { type: String, default: "" },
        },

        data() {
            return {
                icon,
                selectedForm: null,
                fieldLabels: [],
                fieldNames: [],
                records: [],
            };
        },

        computed: {
            hasRecords() {
                return this.records?.data?.length > 0;
            },
        },

        mounted() {
            this.selectedForm = head(Object.keys(this.data.formLists));

            this.getRecords();
        },

        methods: {
            getRecords(url = null) {
                const self = this;

                url = url ? url : route('admin.api.form-builders.entries', self.selectedForm);

                axios.get(url)
                    .then((response) => {
                        self.fieldLabels = response.data.fieldLabels;
                        self.fieldNames = response.data.fieldNames;
                        self.records = response.data.records;
                    })
            },

            onSelectedFormChange() {
                this.getRecords();
            },
        }
    }
</script>