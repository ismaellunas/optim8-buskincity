<template>
    <div>
        <biz-loader
            v-model="isLoading"
            :is-full-page="false"
        />

        <div ref="eventTableScrollPoint" />

        <div class="columns">
            <div class="column is-3">
                <biz-filter-date-range
                    v-model="dateRange"
                    auto-apply
                    input-class-name="input"
                    max-range="7"
                    :clearable="true"
                    :format="'MMM d'"
                    :max-date="setting.maxDate"
                    :min-date="setting.minDate"
                    :month-change-on-scroll="false"
                    :year-range="setting.yearRange"
                />
            </div>

            <div
                v-if="cityOptions.length"
                class="column is-3"
            >
                <biz-select
                    v-model="selectedCity"
                    class="is-fullwidth"
                >
                    <option
                        v-for="city in cityOptions"
                        :key="city.id"
                        :value="city.id"
                    >
                        {{ city.value }}
                    </option>
                </biz-select>
            </div>

            <div class="column is-3">
                <biz-button
                    class="is-primary"
                    type="button"
                    @click.prevent="getRecords(null, true, $event)"
                >
                    <span>Search Events</span>
                </biz-button>
            </div>
        </div>

        <biz-table-index
            is-ajax-pagination
            table-class="is-bordered"
            :records="records"
            :query-params="queryParams"
            @on-clicked-pagination="getRecords($event, true)"
        >
            <template #thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Address</th>
                    <th>Direction</th>
                </tr>
            </template>

            <tr
                v-for="event in records.data"
                :key="event.id"
            >
                <td class="event-date">
                    {{ event.date }} <br>
                    {{ event.time }} ({{ event.timezone }})
                </td>
                <td>{{ event.name }}</td>
                <td>{{ event.description }}</td>
                <td>{{ event.location.address }}, {{ event.location.city }}</td>
                <td>
                    <a
                        v-if="event.direction_url"
                        class="button is-link my-0"
                        target="_blank"
                        :href="event.direction_url"
                    >
                        <span>Direction</span>
                    </a>
                </td>
            </tr>
        </biz-table-index>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizFilterDateRange from '@/Biz/Filter/DateRange';
    import BizLoader from '@/Biz/Loader';
    import BizSelect from '@/Biz/Select';
    import BizTableIndex from '@/Biz/TableIndex';
    import { ref } from 'vue';

    export default {
        name: 'BookingEvents',

        components: {
            BizButton,
            BizFilterDateRange,
            BizLoader,
            BizSelect,
            BizTableIndex,
        },

        props: {
            getRecordUrl: { type: [String, null], required: true },
        },

        setup(props) {
            return {
                cityOptions: ref([]),
                isLoading: ref(false),
                queryParams: ref({}),
                records: ref({}),
                setting: ref({}),
            };
        },

        computed: {
            dateRange: {
                get() {
                    return this.queryParams?.dates ?? null;
                },
                set(newValue) {
                    this.queryParams.dates = newValue;
                }
            },

            selectedCity: {
                get() {
                    return this.queryParams?.city ?? null;
                },
                set(newValue) {
                    this.queryParams.city = newValue;
                }
            }
        },

        mounted() {
            this.getRecords();
        },

        methods: {
            getRecords(url = null, scroll = false) {
                const self = this;

                self.isLoading = true;

                axios
                    .get(url ?? this.getRecordUrl, {
                        params: this.queryParams,
                    })
                    .then((response) => {
                        self.records = response.data.records;
                        self.cityOptions = response.data.options;
                        self.setting = response.data.setting;

                        if (scroll) {
                            self
                                .$refs["eventTableScrollPoint"]
                                .scrollIntoView({behavior: "smooth", block: 'center' })
                        }
                    })
                    .then(() => {
                        self.isLoading = false;
                    });
            },
        },
    };
</script>

<style scoped>
    table .event-date {
        min-width: 190px;
    }
</style>
