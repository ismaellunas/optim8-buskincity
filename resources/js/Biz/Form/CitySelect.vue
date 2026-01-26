<template>
    <div>
        <!-- Hybrid mode: Show dropdown search with custom entry option -->
        <biz-form-dropdown-search
            v-if="!useCustomEntry"
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
            
            <!-- Show no results message with custom entry option -->
            <div v-else-if="cities.length === 0 && hasSearched && currentSearchTerm" class="dropdown-item">
                <div class="has-text-grey mb-2">
                    No cities found
                </div>
                <a 
                    class="button is-small is-fullwidth is-link is-light"
                    @click="useCustomCity"
                >
                    <span class="icon is-small">
                        <i class="fas fa-plus"></i>
                    </span>
                    <span>Use "{{ currentSearchTerm }}" as custom city</span>
                </a>
            </div>

            <biz-dropdown-item
                v-for="city in cities"
                :key="city.id"
                @click="selectCity(city)"
            >
                {{ city.name }} ({{ city.country_code }})
            </biz-dropdown-item>

            <!-- Option to enter custom city -->
            <div v-if="cities.length > 0 && hasSearched && allowCustomEntry" class="dropdown-item" style="border-top: 1px solid #dbdbdb; margin-top: 0.5rem; padding-top: 0.5rem;">
                <a 
                    class="button is-small is-fullwidth is-text"
                    @click="switchToCustomEntry"
                >
                    <span class="icon is-small">
                        <i class="fas fa-keyboard"></i>
                    </span>
                    <span>Or type custom city name</span>
                </a>
            </div>
        </biz-form-dropdown-search>

        <!-- Custom entry mode: Simple text input -->
        <biz-form-input
            v-else
            v-model="customCityName"
            :label="label"
            :required="required"
            :message="message"
            placeholder="Enter city name"
            @blur="handleCustomCityBlur"
        >
            <template #afterInput>
                <p class="control">
                    <button 
                        class="button is-light"
                        type="button"
                        @click="switchToDropdown"
                    >
                        <span class="icon is-small">
                            <i class="fas fa-search"></i>
                        </span>
                        <span class="is-hidden-mobile">Search</span>
                    </button>
                </p>
            </template>
        </biz-form-input>
    </div>
</template>

<script>
import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch.vue';
import BizFormInput from '@/Biz/Form/Input.vue';
import BizDropdownItem from '@/Biz/DropdownItem.vue';
import { debounce } from 'lodash';

export default {
    name: 'BizFormCitySelect',
    components: {
        BizFormDropdownSearch,
        BizFormInput,
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
        allowCustomEntry: { type: Boolean, default: true }, // Enable hybrid mode by default
    },
    emits: ['update:modelValue', 'select'],
    data() {
        return {
            cities: [],
            selectedCity: this.initialCity,
            hasSearched: false,
            isLoading: false,
            currentSearchTerm: '',
            useCustomEntry: false,
            customCityName: '',
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
                    // If initial city is just a string (custom entry), set custom mode
                    if (typeof val === 'string') {
                        this.customCityName = val;
                        this.useCustomEntry = true;
                    }
                }
            },
            deep: true,
            immediate: true
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
            this.currentSearchTerm = query || '';
            
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
        },
        useCustomCity() {
            // User wants to use the search term as a custom city
            this.customCityName = this.currentSearchTerm;
            this.useCustomEntry = true;
            this.emitCustomCity(this.currentSearchTerm);
        },
        switchToCustomEntry() {
            // Switch from dropdown to custom text input
            this.useCustomEntry = true;
            this.customCityName = '';
        },
        switchToDropdown() {
            // Switch from custom input back to dropdown
            this.useCustomEntry = false;
            this.customCityName = '';
            this.cities = [];
            this.hasSearched = false;
        },
        handleCustomCityBlur() {
            // When user finishes typing custom city, emit it
            if (this.customCityName && this.customCityName.trim()) {
                this.emitCustomCity(this.customCityName.trim());
            }
        },
        emitCustomCity(cityName) {
            // Emit custom city as a string (not an ID)
            // The parent component will handle storing this as text
            const customCity = {
                name: cityName,
                country_code: this.countryCode || '',
                latitude: null,
                longitude: null,
                isCustom: true
            };
            this.selectedCity = customCity;
            this.$emit('update:modelValue', null); // No ID for custom cities
            this.$emit('select', customCity);
        }
    }
}
</script>

