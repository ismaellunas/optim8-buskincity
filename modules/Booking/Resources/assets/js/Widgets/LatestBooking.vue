<template>
    <div class="column is-full">
        <biz-panel class="is-white is-relative">
            <template #heading>
                <div class="columns">
                    <div class="column">
                        {{ capitalCase(title) }}
                    </div>
                </div>
            </template>

            <template #default>
                <biz-loader
                    v-model="isLoading"
                    :is-full-page="false"
                />

                <biz-panel-block>
                    <div class="field is-grouped is-grouped-multiline">
                        <div class="control">
                            <biz-select
                                v-model="search.status"
                                class="is-small"
                                @change="getRecords()"
                            >
                                <option
                                    v-for="status in statusOptions"
                                    :key="status.id"
                                    :value="status.id"
                                >
                                    &nbsp; {{ status.value }}
                                </option>
                            </biz-select>
                        </div>

                        <div class="control">
                            <biz-select
                                v-model="search.location"
                                class="is-small"
                                :placeholder="i18n.any"
                                @change="getRecords()"
                            >
                                <option
                                    v-for="location in computedLocationOptions"
                                    :key="location.id"
                                    :value="location.id"
                                >
                                    {{ location.value }}
                                </option>
                            </biz-select>
                        </div>

                        <div class="control">
                            <biz-filter-date-range
                                v-model="dates"
                                class="dashboard-widget-datepicker"
                                input-class-name="input is-small"
                                max-range="31"
                                style="width: 210px"
                                @update:model-value="getRecords()"
                            />
                        </div>

                        <div class="control">
                            <biz-input
                                v-model="search.term"
                                class="is-small"
                                placeholder="Search..."
                                maxlength="255"
                                @keyup.prevent="onSearch(search.term)"
                            />
                        </div>
                    </div>
                </biz-panel-block>

                <biz-panel-block>
                    <div
                        class="table-container"
                        style="width: 100%"
                    >
                        <biz-table is-fullwidth>
                            <thead>
                                <tr>
                                    <th>{{ i18n.status }}</th>
                                    <th>{{ i18n.name }}</th>
                                    <th>{{ i18n.user }}</th>
                                    <th>{{ i18n.date }}</th>
                                    <th>{{ i18n.time }}</th>
                                    <th>{{ i18n.location }}</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template
                                    v-if="isRecordsEmpty"
                                >
                                    <tr>
                                        <td
                                            class="has-text-centered"
                                            colspan="7"
                                        >
                                            {{ i18n.no_data }}
                                        </td>
                                    </tr>
                                </template>

                                <template
                                    v-else
                                >
                                    <tr
                                        v-for="(record, index) in records"
                                        :key="index"
                                    >
                                        <td>{{ record.status }}</td>
                                        <td>{{ record.product_name }}</td>
                                        <td>{{ record.customer_name }}</td>
                                        <td>{{ record.date }}</td>
                                        <td>{{ record.start_end_time }}</td>
                                        <td>{{ record.location ?? '-' }}</td>
                                        <td>
                                            <biz-button-link
                                                v-if="record.can.read"
                                                class="is-primary is-outlined is-small"
                                                title="Detail"
                                                :href="route(data.baseRouteName+'.show', record.id)"
                                            >
                                                {{ i18n.view_detail }}
                                            </biz-button-link>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </biz-table>
                    </div>
                </biz-panel-block>

                <biz-panel-block>
                    <div
                        class="level"
                        style="width: 100%"
                    >
                        <div class="level-left" />
                        <div class="level-right">
                            <biz-button-link
                                class="is-primary is-outlined is-small"
                                :href="route(data.baseRouteName + '.index')"
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
    import BizFilterDateRange from '@/Biz/Filter/DateRange.vue';
    import BizInput from '@/Biz/Input.vue';
    import BizLoader from '@/Biz/Loader.vue';
    import BizPanel from '@/Biz/Panel.vue';
    import BizPanelBlock from '@/Biz/PanelBlock.vue';
    import BizSelect from '@/Biz/Select.vue';
    import BizTable from '@/Biz/Table.vue';
    import icon from '@/Libs/icon-class';
    import { debounce, each } from 'lodash';
    import { debounceTime } from '@/Libs/defaults';
    import { capitalCase } from 'change-case';

    export default {
        name: 'LatestBookingWidget',

        components: {
            BizButtonLink,
            BizFilterDateRange,
            BizInput,
            BizLoader,
            BizPanel,
            BizPanelBlock,
            BizSelect,
            BizTable,
        },

        props: {
            data: { type: Object, required: true },
            i18n: { type: Object, default: () => ({
                status :'Status',
                name :'Name',
                user :'User',
                date :'Date',
                time :'Time',
                location :'Location',
                any :'Any',
                view_detail :'View detail',
                view_all :'View all',
                no_data :'No data',
                search :'Search',
            }) },
            title: { type: String, default: "" },
        },

        data() {
            return {
                isLoading: false,
                icon,
                records: [],
                search: {
                    term: null,
                    status: null,
                    location: null,
                },
                dates: [],
                statusOptions: this.data.statusOptions ?? [],
                locationOptions: this.data.locationOptions ?? [],
            };
        },

        computed: {
            isRecordsEmpty() {
                return this.records.length == 0;
            },

            totalStatusSelected() {
                return this.search.statuses.length;
            },

            computedLocationOptions() {
                const options = [];

                each(this.locationOptions, (location, key) => {
                    options.push({
                        id: key,
                        value: location.country,
                    });

                    each(location.cities, (city) => {
                        options.push({
                            id: key +'-'+ city,
                            value: ' - '+ city,
                        });
                    });
                });

                return options;
            },

            locationParts() {
                const countryCity = {
                    country: null,
                    city: null,
                };

                if (!this.search.location) {
                    return countryCity;
                }

                const locationParts = this.search.location.split('-');

                return {
                    country: locationParts[0],
                    city: locationParts[1],
                };
            },
        },

        mounted() {
            this.getRecords();
        },

        methods: {
            getRecords() {
                const self = this;

                self.isLoading = true,

                axios.get(
                    route('api.booking.widget.latest-bookings'),
                    {
                        params: {
                            term: self.search.term,
                            status: self.search.status,
                            country: self.locationParts.country,
                            city: self.locationParts.city,
                            dates: self.dates.filter(Boolean),
                        },
                    }
                )
                    .then((response) => {
                        self.records = response.data.records;
                        self.statusOptions = response.data.options.status;
                        self.locationOptions = response.data.options.location;
                    })
                    .then(() => {
                        self.isLoading = false;
                    });
            },

            onSearch: debounce(function(term = '') {
                if (term.length > 2 || term.length == 0) {
                    this.getRecords();
                }
            }, debounceTime),

            capitalCase,
        }
    }
</script>