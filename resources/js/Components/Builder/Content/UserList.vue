<template>
    <div class="pb-user-list">
        <div class="columns is-multiline">
            <div class="column is-12">
                <div class="field is-grouped is-grouped-multiline">
                    <div class="control">
                        <p class="has-text-weight-bold is-size-7 is-uppercase pt-1">
                            Filters
                        </p>
                    </div>

                    <div class="control">
                        <biz-select
                            v-model="filter.order_by"
                            class="select is-small"
                            placeholder="Order by"
                            @change="selectOrderBy()"
                        >
                            <option
                                v-for="orderByOption in orderByOptions"
                                :key="orderByOption.id"
                                :value="orderByOption.id"
                            >
                                {{ orderByOption.value }}
                            </option>
                        </biz-select>
                    </div>

                    <div class="control">
                        <biz-select
                            v-model="filter.country"
                            class="select is-small"
                            placeholder="Country"
                            @change="selectCountry()"
                        >
                            <option
                                v-for="countryOption in filteredCountryOptions"
                                :key="countryOption.id"
                                :value="countryOption.id"
                            >
                                {{ countryOption.value }}
                            </option>
                        </biz-select>
                    </div>

                    <div
                        v-if="canFilteredByType"
                        class="control"
                    >
                        <biz-select
                            v-model="filter.type"
                            class="select is-small"
                            placeholder="Type"
                            @change="selectType()"
                        >
                            <option
                                v-for="typeOption in filteredTypeOptions"
                                :key="typeOption.id"
                                :value="typeOption.id"
                            >
                                {{ typeOption.value }}
                            </option>
                        </biz-select>
                    </div>
                </div>
            </div>

            <template
                v-for="user in users"
                :key="user.unique_key"
            >
                <slot :user="user" />
            </template>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizSelect from '@/Biz/Select';
    import { union, isEmpty, forEach } from 'lodash';

    export default {
        components: {
            BizSelect,
        },

        mixins: [
            MixinHasLoader
        ],

        props: {
            defaultCountries: { type: Array, default: () => [] },
            defaultTypes: { type: Array, default: () => [] },
            defaultOrderBy: { type: String, default: null },
            excludedId: { type: String, default: "" },
            orderByOptions: { type: Array, default: () => [] },
            roles: { type: String, default: "" },
            url: { type: String, required: true },
            metas: {
                type: Array,
                default: () => [
                    'discipline',
                    'stage_name',
                ],
            },
        },

        data() {
            return {
                users: [],
                country: null,
                orderBy: this.defaultOrderBy,
                filter: {
                    order_by: null,
                    country: null,
                    type: null,
                },
                type: null,
                options: {
                    countries: [],
                    types: [],
                },
            };
        },

        computed: {
            selectedCountries() {
                let countries = [];

                countries.push(this.country);

                if (this.defaultCountries) {
                    countries = union(countries, this.defaultCountries);
                }

                return countries.filter(Boolean);
            },

            selectedTypes() {
                let types = [];

                types.push(this.type);

                if (this.defaultTypes) {
                    types = union(types, this.defaultTypes);
                }

                return types.filter(Boolean);
            },

            canFilteredByType() {
                return !isEmpty(this.options.types);
            },

            isFilteredCountryOnBackend() {
                return !isEmpty(this.defaultCountries);
            },

            isFilteredTypeOnBackend() {
                return !isEmpty(this.defaultTypes);
            },

            filteredCountryOptions() {
                let options = [];
                let countries = this.defaultCountries;

                if (this.isFilteredCountryOnBackend) {
                    forEach(this.options.countries, function (country) {
                        if (countries.includes(country.id)) {
                            options.push(country);
                        }
                    })

                    return options;
                }

                return this.options.countries;
            },

            filteredTypeOptions() {
                let options = [];
                let types = this.defaultTypes;

                if (this.isFilteredTypeOnBackend) {
                    forEach(this.options.types, function (type) {
                        if (types.includes(type.id)) {
                            options.push(type);
                        }
                    })

                    return options;
                }

                return this.options.types;
            },
        },

        mounted() {
            this.load();
        },

        methods: {
            load() {
                const self = this;

                self.onStartLoadingOverlay();

                return axios
                    .get(this.url, {
                        params: {
                            country: self.country,
                            default_countries: self.defaultCountries,
                            default_types: self.defaultTypes,
                            excluded_user: self.excludedId,
                            order_by: self.orderBy,
                            roles: self.roles,
                            type: self.type,
                        }
                    })
                    .then(function(response) {
                        self.users = response.data.users;
                        self.options.countries = response.data.options.countries;
                        self.options.types = response.data.options.types;
                    })
                    .catch(function(error) {
                        self.users = [];
                        self.options.countries = [];
                        self.options.types = [];
                    })
                    .then(function () {
                        self.onEndLoadingOverlay();
                    });
            },

            selectOrderBy() {
                this.orderBy = this.filter.order_by;
                this.load();
            },

            selectCountry() {
                this.country = this.filter.country;
                this.load();
            },

            selectType() {
                this.type = this.filter.type;
                this.load();
            },
        },
    }

</script>
