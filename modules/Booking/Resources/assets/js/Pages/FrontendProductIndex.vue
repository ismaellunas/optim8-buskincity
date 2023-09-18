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
            :records="products"
            :query-params="queryParams"
        >
            <template #thead>
                <tr>
                    <biz-table-column-sort
                        :order="order"
                        :is-sorted="column == 'name'"
                        @click="orderColumn('name')"
                    >
                        Name
                    </biz-table-column-sort>
                    <biz-table-column-sort
                        :order="order"
                        :is-sorted="column == 'city'"
                        @click="orderColumn('city')"
                    >
                        City
                    </biz-table-column-sort>
                    <biz-table-column-sort
                        :order="order"
                        :is-sorted="column == 'country'"
                        @click="orderColumn('country')"
                    >
                        Country
                    </biz-table-column-sort>
                    <th>
                        <div class="level-right">
                            Actions
                        </div>
                    </th>
                </tr>
            </template>

            <tr
                v-for="product in products.data"
                :key="product.id"
            >
                <td>{{ product.name }}</td>
                <td>{{ product.city }}</td>
                <td>{{ product.country }}</td>
                <td>
                    <div class="level-right">
                        <biz-button-link
                            class="is-ghost has-text-black"
                            :href="route(baseRouteName+'.show', product.id)"
                        >
                            <biz-icon
                                class="is-small"
                                :icon="calendarCirclePlusIcon"
                            />
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
            products: { type: Object, required: true },
            countryOptions: { type: Array, default: () => [] },
            cityOptions: { type: Array, default: () => [] },
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
