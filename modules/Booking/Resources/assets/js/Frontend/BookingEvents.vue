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
                    <th>Directions</th>
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
                <td>
                    <div>
                        <p v-html="event.short_description" />

                        <a
                            href="#"
                            class="has-text-primary has-text-weight-bold"
                            @click.prevent="selectedEvent = event"
                        >
                            Read More
                        </a>
                    </div>
                </td>
                <td>{{ event.location.address }}, {{ event.location.city }}</td>
                <td>
                    <a
                        v-if="event.direction_url"
                        class="button is-link my-0"
                        target="_blank"
                        :href="event.direction_url"
                    >
                        <span>Directions</span>
                    </a>
                </td>
            </tr>

            <tr v-if="!records.data || records.data.length <= 0">
                <td
                    colspan="100"
                    class="has-text-centered"
                >
                    ...
                </td>
            </tr>
        </biz-table-index>

        <biz-modal-card
            v-if="selectedEvent"
            :is-close-hidden="true"
            @click.prevent="closeDescriptionModal"
        >
            <template #header>
                <p class="modal-card-title has-text-weight-bold">
                    {{ selectedEvent.name }}
                </p>

                <button
                    aria-label="close"
                    class="delete"
                    @click.prevent="closeDescriptionModal"
                />
            </template>

            <div v-html="selectedEvent.description" />
        </biz-modal-card>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizFilterDateRange from '@/Biz/Filter/DateRange.vue';
    import BizLoader from '@/Biz/Loader.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizSelect from '@/Biz/Select.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import { ref } from 'vue';

    export default {
        name: 'BookingEvents',

        components: {
            BizButton,
            BizFilterDateRange,
            BizLoader,
            BizModalCard,
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
                selectedEvent: ref(null),
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
                                .scrollIntoView({behavior: "smooth" })
                        }
                    })
                    .catch((error) => {
                        console.error(error);
                        self.records = self.setting = {};
                        self.cityOptions = [];
                    })
                    .then(() => {
                        self.isLoading = false;
                    });
            },

            closeDescriptionModal() {
                this.selectedEvent = null;
            },
        },
    };
</script>

<style scoped>
    table .event-date {
        min-width: 190px;
    }
</style>
