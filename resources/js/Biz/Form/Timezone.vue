<template>
    <div>
        <biz-form-dropdown-search
            :label="label"
            :close-on-click="true"
            :disabled="disabled"
            :message="message"
            :required="required"
            @search="searchOptions($event)"
        >
            <template #trigger>
                <span
                    class="has-text-left"
                    :style="{'min-width': '20rem'}"
                >
                    {{ selectedTimezone }}
                </span>
            </template>

            <div style="max-height: 30rem; overflow-y: scroll">
                <biz-dropdown-item
                    v-for="option in filteredOptions"
                    :key="option.id"
                    @click="timezone = option.id"
                >
                    {{ timezoneValueFormatter(option.value) }}
                </biz-dropdown-item>
            </div>
        </biz-form-dropdown-search>
    </div>
</template>

<script>
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import { debounceTime } from '@/Libs/defaults';
    import { find, debounce, isEmpty, filter } from 'lodash';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: "BizFormTimezone",

        components: {
            BizDropdownItem,
            BizFormDropdownSearch,
        },

        props: {
            disabled: { type: Boolean, default: false },
            label: { type: String, default: null },
            message: { type: [Array, Object, String], default: undefined },
            modelValue: { type: [String, Number, null], required: true },
            required: { type: Boolean, default: false },
        },

        setup(props, { emit }) {
            return {
                filteredOptions: ref([]),
                options: ref([]),
                timezone: useModelWrapper(props, emit),
            };
        },

        computed: {
            selectedTimezone() {
                const selectedTimezone = find(this.options, {id: this.timezone});

                if (selectedTimezone) {
                    return this.timezoneValueFormatter(selectedTimezone.value);
                }

                return null;
            },
        },

        beforeMount() {
            this.loadOptions();
        },

        methods: {
            loadOptions() {
                axios
                    .get(route('admin.api.options.timezones'))
                    .then((response) => {
                        this.options = response?.data ?? [];

                        this.filteredOptions = this.options;
                    });
            },

            searchOptions: debounce(function(term) {
                if (! isEmpty(term) && term.length > 1) {
                    this.filteredOptions = filter(this.options, function (option) {
                        const termRegex = new RegExp(term, 'i');

                        return (
                            termRegex.test(option.value.offsetValue)
                            || termRegex.test(option.value.timezone)
                        );
                    });
                } else {
                    this.filteredOptions = this.options;
                }
            }, debounceTime),

            timezoneValueFormatter(value) {
                return `(${value.offsetValue}) ${value.timezone}`;
            },
        },
    };
</script>
