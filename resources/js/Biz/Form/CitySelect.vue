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

        <!-- Show hint when no search yet -->
        <div v-if="cities.length === 0 && !hasSearched && !isLoading" class="dropdown-item has-text-grey">
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
    watch: {
        initialCity: {
            handler(val) {
                if (val) {
                    this.selectedCity = val;
                }
            },
            deep: true
        },
    },
    methods: {
        onSearch: debounce(function(query) {
            // Reset state when clearing search
            if (!query || query.length === 0) {
                this.cities = [];
                this.hasSearched = false;
                this.isLoading = false;
                return;
            }
            
            // Start search immediately on any input (1+ chars)
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
        }, 200), // Short debounce for responsiveness
        selectCity(city) {
            this.selectedCity = city;
            this.$emit('update:modelValue', city.id);
            this.$emit('select', city);
        }
    }
}
</script>
