<template>
    <div class="pb-user-list">
        <div class="columns is-multiline">
            <div class="column is-12">
                <div class="field is-grouped">
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
                                v-for="countryOption in countryOptions"
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
                                v-for="typeOption in typeOptions"
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
    import BizSelect from '@/Biz/Select';
    import { union, isEmpty } from 'lodash';

    export default {
        components: {
            BizSelect,
        },

        props: {
            countries: { type: Array, default: () => [] },
            countryOptions: { type: Array, default: () => [] },
            typeOptions: { type: Array, default: () => [] },
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
            };
        },

        computed: {
            selectedCountries() {
                let countries = [];

                countries.push(this.country);

                if (this.countries) {
                    countries = union(countries, this.countries);
                }

                return countries.filter(Boolean);
            },

            canFilteredByCountry() {
                return isEmpty(this.countries);
            },

            canFilteredByType() {
                return !isEmpty(this.typeOptions);
            }
        },

        mounted() {
            this.load();
        },

        methods: {
            load() {
                const self = this;

                return axios
                    .get(this.url, {
                        params: {
                            countries: self.selectedCountries,
                            excluded_user: self.excludedId,
                            order_by: self.orderBy,
                            roles: self.roles,
                            type: self.type,
                        }
                    })
                    .then(function(response) {
                        self.users = response.data;
                    })
                    .catch(function(error) {
                        self.users = [];
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
