<template>
    <div>
        <biz-form-dropdown-search
            :label="label"
            :close-on-click="true"
            @search="searchOption($event)"
        >
            <template #trigger>
                <span :style="{'min-width': '4rem'}">
                    Select
                </span>
            </template>

            <biz-dropdown-item
                v-for="option in filteredOptions"
                :key="option"
                @click="selectOption(option)"
            >
                {{ option.value }}
            </biz-dropdown-item>
        </biz-form-dropdown-search>

        <div class="">
            <span
                v-for="(selectedOption) in selectedOptions"
                :key="selectedOption"
                class="tag is-medium m-1"
            >
                {{ selectedOption.value }}
                <button
                    type="button"
                    class="delete is-small"
                    @click="deleteOption(selectedOption)"
                />
            </span>
        </div>
    </div>
</template>

<script>
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch';
    import { debounceTime } from '@/Libs/defaults';
    import { debounce, filter, remove, isEmpty } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizDropdownItem,
            BizFormDropdownSearch,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: Array, default: () => [] },
            settings: { type: Object, default: () => {} },
        },

        emits: ['update:modelValue'],

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                rawOptions: [],
                filteredOptions: [],
            };
        },

        computed: {
            options() {
                if (!isEmpty(this.rawOptions)) {
                    return this.rawOptions;
                } else if (!isEmpty(this.settings.options)) {
                    return this.settings.options;
                }

                return [];
            },

            selectedOptions() {
                const self = this;

                return filter(this.options, (option) => {
                    return self.computedValue.includes(option.id);
                });
            }
        },

        mounted() {
            if (! this.settings.options) {
                let url = null;

                if (this.settings.optionsRoute) {
                    url = route(this.settings.optionsRoute);
                }

                if (url) {
                    const self = this;

                    axios
                        .get(url)
                        .then(function(response) {
                            self.rawOptions = response.data;
                            self.filteredOptions = response.data.slice(0, 10);
                        })
                        .catch(function(error) {
                            self.rawOptions = [];
                            self.filteredOptions = [];
                        });
                }
            }
        },

        methods: {
            selectOption(option) {
                this.computedValue.push(option.id);
            },

            searchOption: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredOptions = filter(this.options, function (option) {
                        return new RegExp(term, 'i').test(option.value);
                    }).slice(0, 10);
                } else {
                    this.filteredOptions = this.options.slice(0, 10);
                }
            }, debounceTime),

            deleteOption(option) {
                remove(this.computedValue, (optionId) => optionId == option.id);
            }
        },
    };
</script>
