<template>
    <div>
        <div ref="eventTableScrollPoint" />

        <biz-loader
            v-model="isLoading"
            :is-full-page="false"
        />

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
                v-if="!setting.isLeaf && options?.spaces && options.spaces.length > 0"
                class="column is-3"
            >
                <biz-select
                    v-model="selectedSpace"
                    class="is-fullwidth"
                >
                    <option
                        v-for="space in options.spaces"
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
                    <biz-tag><b>Start</b></biz-tag> {{ event.started_at }}<br>
                    <biz-tag><b>End</b></biz-tag> {{ event.ended_at }}
                </td>
                <td>{{ event.title }}</td>
                <td>
                    <div>
                        <p v-html="event.short_description" />

                        <a
                            v-if="isReadMoreShown(event.short_description)"
                            href="#"
                            class="has-text-primary has-text-weight-bold"
                            @click.prevent="selectedEvent = event"
                        >
                            Read More
                        </a>
                    </div>
                </td>
                <td>{{ event.address }}</td>
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
                    class="has-text-centered"
                    colspan="100"
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
                    {{ selectedEvent.title }}
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
    import BizTag from '@/Biz/Tag.vue';
    import { ref } from 'vue';
    import { endsWith } from 'lodash';

    export default {
        name: 'SpaceEvents',

        components: {
            BizButton,
            BizFilterDateRange,
            BizLoader,
            BizModalCard,
            BizSelect,
            BizTableIndex,
            BizTag,
        },

        props: {
            getRecordUrl: { type: [String, null], required: true },
        },

        setup(props) {
            return {
                isLoading: ref(false),
                options: ref([]),
                queryParams: ref({}),
                records: ref({}),
                selectedEvent: ref(null),
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
                        self.setting = response.data.setting;
                        self.options = response.data.options;

                        if (scroll) {
                            self
                                .$refs["eventTableScrollPoint"]
                                .scrollIntoView({behavior: "smooth" })
                        }
                    })
                    .catch((error) => {
                        console.error(error);
                        self.records = self.setting = {};
                        self.options = [];
                    })
                    .then(() => {
                        self.isLoading = false;
                    });
            },

            closeDescriptionModal() {
                this.selectedEvent = null;
            },

            isReadMoreShown(shortDescription) {
                return endsWith(shortDescription, '...');
            },
        },
    };
</script>

<style scoped>
    table .space-event-date {
        min-width: 210px;
    }
</style>
