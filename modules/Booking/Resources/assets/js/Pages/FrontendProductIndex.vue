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
                    v-model="location"
                    class="is-fullwidth"
                    placeholder="Any"
                    @change="onLocationChanged()"
                >
                    <option
                        v-for="locationOption in computedLocationOptions"
                        :key="locationOption.id"
                        :value="locationOption.id"
                    >
                        {{ locationOption.value }}
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
                                :icon="icon.calendarCirclePlus"
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
    import icon from '@/Libs/icon-class';
    import { merge, each } from 'lodash';
    import { ref } from "vue";

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
            locationOptions: { type: Object, default: () => {} },
        },

        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            const country = props.pageQueryParams?.country;
            const city = props.pageQueryParams?.city;
            const location = country
                ? country + (city ? '-' + city : '')
                : null;

            return {
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? ""),
                location: ref(location),
            };
        },

        data() {
            return {
                icon
            };
        },

        computed: {
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

                if (!this.location) {
                    return countryCity;
                }

                const locationParts = this.location.split('-');

                return {
                    country: locationParts[0],
                    city: locationParts[1],
                };
            },
        },

        methods: {
            onLocationChanged() {
                this.queryParams['city'] = this.locationParts.city;
                this.queryParams['country'] = this.locationParts.country;
                this.refreshWithQueryParams(); // on mixin MixinHasColumnSorted
            }
        },
    };
</script>
