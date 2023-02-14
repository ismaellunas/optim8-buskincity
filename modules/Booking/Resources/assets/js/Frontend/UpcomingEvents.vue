<template>
    <div>
        <biz-loader
            v-model="isLoading"
            :is-full-page="false"
        />

        <div ref="spaceEventTableScrollPoint" />

        <div class="columns">
            <div class="column is-3">
                <biz-filter-date-range
                    v-model="dateRange"
                    auto-apply
                    input-class-name="input"
                    max-range="7"
                    :clearable="true"
                    :format="'MMM d'"
                    :max-date="maxDate"
                    :min-date="minDate"
                    :month-change-on-scroll="false"
                    :year-range="yearRange"
                />
            </div>

            <div
                v-if="!isLeaf"
                class="column is-3"
            >
                <biz-select
                    v-model="selectedSpace"
                    class="is-fullwidth"
                >
                    <option
                        v-for="space in spaceOptions"
                        :key="space.id"
                        :value="space.id"
                    >
                        {{ space.value }}
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
                <td class="space-event-date">
                    {{ $event['date'] }} <br>
                    {{ $event['time'] }} ({{ $event['timezone'] }})
                </td>
                <td>{{ event.title }}</td>
                <td>{{ event.description }}</td>
                <td>{{ event.address }}</td>
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
    import BizTag from '@/Biz/Tag';
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
            spaceId: { type: [Number, String], required: true },
        },

        setup(props) {
            return {
                isLoading: ref(false),
                isLeaf: ref(true),
                maxDate: ref(null),
                minDate: ref(null),
                queryParams: ref({}),
                records: ref({}),
                spaceOptions: ref([]),
                yearRange: ref([]),
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

            selectedSpace: {
                get() {
                    return this.queryParams?.space ?? null;
                },
                set(newValue) {
                    this.queryParams.space = newValue;
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
                        self.minDate = response.data.minDate;
                        self.maxDate = response.data.maxDate;
                        self.spaceOptions = response.data.options;
                        self.yearRange = response.data.yearRange;
                        self.isLeaf = response.data.isLeaf;

                        if (scroll) {
                            self
                                .$refs["spaceEventTableScrollPoint"]
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
    table .space-event-date {
        min-width: 210px;
    }
</style>
