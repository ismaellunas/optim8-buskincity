<template>
    <biz-form-dropdown-search
        :label="label"
        :required="required"
        :message="message"
        :placeholder="placeholder"
        @search="onSearch"
    >
        <template #trigger>
            <span v-if="selectedCity">
                {{ selectedCity.name }} ({{ selectedCity.country_code }})
            </span>
            <span v-else class="has-text-grey-light">
                {{ placeholder }}
            </span>
        </template>

        <!-- Show hint when no search yet (only for API search, not restricted) -->
        <div v-if="cities.length === 0 && !hasSearched && !isLoading && !isRestricted" class="dropdown-item has-text-grey">
            Type to search cities...
        </div>
        
        <!-- Show loading state -->
        <div v-else-if="isLoading" class="dropdown-item has-text-grey">
            Searching...
        </div>
        
        <!-- Show no results message -->
        <div v-else-if="cities.length === 0 && hasSearched" class="dropdown-item has-text-grey">
            No cities found
        </div>

        <biz-dropdown-item
            v-for="city in cities"
            :key="city.id"
            @click="selectCity(city)"
        >
            {{ city.name }} ({{ city.country_code }})
        </biz-dropdown-item>
    </biz-form-dropdown-search>
</template>

<script>
import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch.vue';
import BizDropdownItem from '@/Biz/DropdownItem.vue';
import { debounce } from 'lodash';

export default {
    name: 'BizFormCitySelect',
    components: {
        BizFormDropdownSearch,
        BizDropdownItem,
    },
    props: {
        modelValue: { type: [Number, String], default: null },
        label: { type: String, default: 'City' },
        required: { type: Boolean, default: false },
        message: { type: String, default: null },
        placeholder: { type: String, default: 'Search for a city...' },
        countryCode: { type: String, default: null },
        initialCity: { type: Object, default: null },
        restrictedCities: { type: Array, default: () => [] },
    },
    emits: ['update:modelValue', 'select'],
    data() {
        return {
            cities: [],
            selectedCity: this.initialCity,
            hasSearched: false,
            isLoading: false,
        };
    },
    computed: {
        isRestricted() {
            return this.restrictedCities && this.restrictedCities.length > 0;
        }
    },
    watch: {
        initialCity: {
            handler(val) {
                if (val) {
                    this.selectedCity = val;
                }
            },
            deep: true
        },
        // When restricted cities are provided, show them immediately
        restrictedCities: {
            handler(cities) {
                if (cities && cities.length > 0) {
                    this.cities = cities.map(c => ({
                        id: c.id,
                        name: c.name,
                        country_code: c.country_code
                    }));
                }
            },
            immediate: true
        }
    },
    methods: {
        onSearch: debounce(function(query) {
            // If using restricted cities, filter locally
            if (this.isRestricted) {
                if (!query || query.length === 0) {
                    this.cities = this.restrictedCities.map(c => ({
                        id: c.id,
                        name: c.name,
                        country_code: c.country_code
                    }));
                    this.hasSearched = false;
                    return;
                }
                
                const searchTerm = query.toLowerCase();
                this.cities = this.restrictedCities
                    .filter(c => 
                        c.name.toLowerCase().includes(searchTerm) ||
                        c.country_code.toLowerCase().includes(searchTerm)
                    )
                    .map(c => ({
                        id: c.id,
                        name: c.name,
                        country_code: c.country_code
                    }));
                this.hasSearched = true;
                return;
            }
            
            // Normal API-based search
            if (!query || query.length === 0) {
                this.cities = [];
                this.hasSearched = false;
                this.isLoading = false;
                return;
            }
            
            this.isLoading = true;
            this.hasSearched = true;
            
            axios.get(route('admin.api.cities.index'), {
                params: {
                    search: query,
                    country_code: this.countryCode
                }
            }).then(response => {
                this.cities = response.data;
            }).finally(() => {
                this.isLoading = false;
            });
        }, 200),
        selectCity(city) {
            this.selectedCity = city;
            this.$emit('update:modelValue', city.id);
            this.$emit('select', city);
        }
    }
}
</script>

