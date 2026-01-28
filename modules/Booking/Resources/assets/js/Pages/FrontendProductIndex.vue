<template>
    <div class="box">
        <div class="columns is-multiline is-mobile">
            <div class="column is-4-desktop is-6-tablet is-12-mobile">
                <biz-filter-search
                    v-model="term"
                    @search="search"
                />
            </div>

            <div class="column is-2-desktop is-3-tablet is-12-mobile">
                <biz-select
                    v-model="city"
                    class="is-fullwidth"
                    placeholder="City"
                    @change="onCityChanged()"
                >
                    <option
                        v-for="cityOption in cityOptions"
                        :key="cityOption.value"
                        :value="cityOption.value"
                    >
                        {{ cityOption.name }}
                    </option>
                </biz-select>
            </div>

            <div class="column is-2-desktop is-3-tablet is-12-mobile">
                <biz-select
                    v-model="country"
                    class="is-fullwidth"
                    placeholder="Country"
                    @change="onCountryChanged()"
                >
                    <option
                        v-for="countryOption in countryOptions"
                        :key="countryOption.value"
                        :value="countryOption.value"
                    >
                        {{ countryOption.name }}
                    </option>
                </biz-select>
            </div>
        </div>

        <biz-table-index
            :records="events"
            :query-params="queryParams"
        >
            <template #thead>
                <tr>
                    <th>Event Name</th>
                    <th>Pitch</th>
                    <th>Event Dates</th>
                    <th>City</th>
                    <th>Country</th>
                    <th>
                        <div class="level-right">
                            Actions
                        </div>
                    </th>
                </tr>
            </template>

            <tr
                v-for="event in events.data"
                :key="event.id"
            >
                <td>
                    <div class="content">
                        <strong>{{ event.title }}</strong>
                        <p
                            v-if="event.excerpt || event.description"
                            class="is-size-7 has-text-grey"
                        >
                            {{ event.excerpt || event.description }}
                        </p>
                    </div>
                </td>
                <td>{{ event.pitch_name }}</td>
                <td>{{ event.started_at }} - {{ event.ended_at }}</td>
                <td>{{ event.city }}</td>
                <td>{{ event.country }}</td>
                <td>
                    <div class="level-right">
                        <biz-button-link
                            class="is-primary"
                            :href="route(baseRouteName+'.show', event.id)"
                        >
                            <biz-icon
                                class="is-small"
                                :icon="calendarCirclePlusIcon"
                            />
                            <span>
                                {{ i18n.book_now }}
                            </span>
                        </biz-button-link>
                    </div>
                </td>
            </tr>
        </biz-table-index>
    </div>
</template>

<script>
    import MixinHasColumnSorted from '@/Mixins/HasColumnSorted';
    import Layout from '@/Layouts/User.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTableColumnSort from '@/Biz/TableColumnSort.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import BizSelect from '@/Biz/Select.vue';
    import { calendarCirclePlus as calendarCirclePlusIcon } from '@/Libs/icon-class';
    import { merge, filter, find } from 'lodash';
    import { ref, computed } from "vue";

    export default {
        components: {
            BizButtonLink,
            BizFilterSearch,
            BizIcon,
            BizTableColumnSort,
            BizTableIndex,
            BizSelect,
        },

        mixins: [
            MixinHasColumnSorted,
        ],

        layout: Layout,

        props: {
            baseRouteName: { type: String, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            events: { type: Object, required: true },
            countryOptions: { type: Array, default: () => [] },
            cityOptions: { type: Array, default: () => [] },
            i18n: { type: Object, required: true },
        },

        setup(props) {
            const queryParams = computed(() => merge(
                {},
                props.pageQueryParams
            ));

            return {
                queryParams: ref(queryParams),
                term: ref(queryParams.value?.term ?? ""),
                country: ref(queryParams.value?.country ?? null),
                city: ref(queryParams.value?.city ?? null),
            };
        },

        data() {
            return {
                calendarCirclePlusIcon,
            };
        },

        methods: {
            onCountryChanged() {
                this.city = null;

                this.queryParams['country'] = this.country;
                this.queryParams['city'] = null;

                this.refreshWithQueryParams(); // on mixin MixinHasColumnSorted
            },

            onCityChanged() {
                const self = this;

                let findCountryCode = find(self.cityOptions, { value: self.city })?.country_code;

                if (typeof findCountryCode !== 'undefined') {
                    self.country = find(self.cityOptions, { value: self.city }).country_code;
                }

                self.queryParams['country'] = self.country;
                self.queryParams['city'] = self.city;

                self.refreshWithQueryParams(); // on mixin MixinHasColumnSorted
            }
        },
    };
</script>
