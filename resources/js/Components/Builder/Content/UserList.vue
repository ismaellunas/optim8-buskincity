<template>
    <div class="pb-user-list">
        <div class="columns is-multiline">
            <div class="column">
                <biz-dropdown
                    class="ml-1"
                    :close-on-click="true"
                >
                    <template #trigger>
                        <span>Order By</span>
                        <span class="icon is-small">
                            <i
                                class="fas fa-angle-down"
                                aria-hidden="true"
                            />
                        </span>
                    </template>

                    <biz-dropdown-item
                        tag="a"
                        @click.prevent="selectOrderBy()"
                    >
                        Default
                    </biz-dropdown-item>

                    <hr class="dropdown-divider">

                    <biz-dropdown-item
                        v-for="orderByOption in orderByOptions"
                        :key="orderByOption"
                        tag="a"
                        @click.prevent="selectOrderBy(orderByOption.id)"
                    >
                        {{ orderByOption.value }}
                    </biz-dropdown-item>
                </biz-dropdown>

                <biz-dropdown
                    v-if="canFilteredByCountry"
                    class="ml-1"
                    :close-on-click="true"
                >
                    <template #trigger>
                        <span>Country</span>
                        <span class="icon is-small">
                            <i
                                class="fas fa-angle-down"
                                aria-hidden="true"
                            />
                        </span>
                    </template>

                    <biz-dropdown-item>
                        <a
                            href="#"
                            @click.prevent="selectCountry()"
                        >
                            Default
                        </a>
                    </biz-dropdown-item>

                    <hr class="dropdown-divider">

                    <biz-dropdown-item
                        v-for="countryOption in countryOptions"
                        :key="countryOption"
                        tag="a"
                        @click.prevent="selectCountry(countryOption)"
                    >
                        {{ countryOption.value }}
                    </biz-dropdown-item>
                </biz-dropdown>
            </div>
        </div>

        <div class="columns is-multiline">
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
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import { debounce, union, isEmpty } from 'lodash';
    import { debounceTime } from '@/Libs/defaults';

    export default {
        components: {
            BizDropdown,
            BizDropdownItem,
        },

        props: {
            countries: { type: Array, default: () => [] },
            countryOptions: { type: Array, default: () => [] },
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
                        }
                    })
                    .then(function(response) {
                        self.users = response.data;
                    })
                    .catch(function(error) {
                        self.users = [];
                    });
            },

            selectOrderBy(option) {
                this.orderBy = option;
                this.load();
            },

            selectCountry(option) {
                this.country = option?.id ?? null;
                this.load();
            },
        },
    }

</script>
