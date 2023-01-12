<template>
    <div class="column is-full">
        <biz-panel class="is-white is-relative">
            <template #heading>
                <div class="columns">
                    <div class="column">
                        {{ title }}
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
                            <biz-input
                                v-model="search.term"
                                class="is-small"
                                placeholder="Search..."
                                maxlength="255"
                                @keyup.prevent="onSearch(search.term)"
                            />
                        </div>

                        <div class="control">
                            <biz-dropdown
                                :close-on-click="false"
                                class-button="is-small"
                            >
                                <template #trigger>
                                    <span>Status ({{ totalStatusSelected }})</span>

                                    <span class="icon is-small">
                                        <i
                                            :class="icon.angleDown"
                                            aria-hidden="true"
                                        />
                                    </span>
                                </template>

                                <biz-dropdown-item
                                    v-for="status in data.statusOptions"
                                    :key="status.id"
                                >
                                    <biz-checkbox
                                        v-model:checked="search.statuses"
                                        :value="status.id"
                                        @change="getRecords()"
                                    >
                                        &nbsp; {{ status.value }}
                                    </biz-checkbox>
                                </biz-dropdown-item>
                            </biz-dropdown>
                        </div>

                        <div class="control">
                            <biz-dropdown-search
                                is-small
                                :close-on-click="true"
                                @search="searchCity($event)"
                            >
                                <template #trigger>
                                    <span>
                                        {{ search.city ?? 'Any' }}
                                    </span>
                                </template>

                                <biz-dropdown-item
                                    @click="onCityChange()"
                                >
                                    Any
                                </biz-dropdown-item>

                                <biz-dropdown-item
                                    v-for="(option, index) in filteredCities"
                                    :key="index"
                                    @click="onCityChange(option)"
                                >
                                    {{ option }}
                                </biz-dropdown-item>
                            </biz-dropdown-search>
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
                                    <th>Status</th>
                                    <th>Name</th>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>City</th>
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
                                            Empty
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
                                        <td>{{ record.city ?? '-' }}</td>
                                        <td>
                                            <biz-button-link
                                                class="has-text-black"
                                                title="Detail"
                                                :href="route(data.baseRouteName+'.show', record.id)"
                                                :disabled="!record.can.read"
                                            >
                                                <biz-icon
                                                    class="is-small"
                                                    :icon="icon.show"
                                                />
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
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizDropdownSearch from '@/Biz/DropdownSearch';
    import BizIcon from '@/Biz/Icon';
    import BizInput from '@/Biz/Input';
    import BizLoader from '@/Biz/Loader';
    import BizPanel from '@/Biz/Panel';
    import BizPanelBlock from '@/Biz/PanelBlock';
    import BizTable from '@/Biz/Table';
    import icon from '@/Libs/icon-class';
    import { debounceTime } from '@/Libs/defaults';
    import { debounce, isEmpty, filter } from 'lodash';

    export default {
        name: 'LatestBookingWidget',

        components: {
            BizButtonLink,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizDropdownSearch,
            BizIcon,
            BizInput,
            BizLoader,
            BizPanel,
            BizPanelBlock,
            BizTable,
        },

        props: {
            data: { type: Object, required: true },
            title: { type: String, default: "" },
        },

        data() {
            return {
                isLoading: false,
                icon,
                records: [],
                search: {
                    term: null,
                    statuses: [],
                    city: null,
                },
                filteredCities: this.data.cityOptions.slice(0, 10),
            };
        },

        computed: {
            isRecordsEmpty() {
                return this.records.length == 0;
            },

            totalStatusSelected() {
                return this.search.statuses.length;
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
                            status: self.search.statuses,
                            city: self.search.city,
                        },
                    }
                )
                    .then((response) => {
                        self.records = response.data.records;
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

            searchCity: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredCities = filter(this.data.cityOptions, function (city) {
                        return new RegExp(term, 'i').test(city);
                    }).slice(0, 10);
                } else {
                    this.filteredCities = this.data.cityOptions.slice(0, 10);
                }
            }, debounceTime),

            onCityChange(city = null) {
                this.search.city = city;

                this.getRecords();
            },
        }
    }
</script>