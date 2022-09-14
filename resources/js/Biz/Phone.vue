<template>
    <div class="field has-addons">
        <p class="control">
            <biz-dropdown-search
                ref="dropdownSearch"
                label="Country"
                :close-on-click="true"
                @click="focus()"
                @search="searchCountry($event)"
            >
                <template #trigger>
                    <span class="icon is-small">
                        {{ selectedCountry.id }}
                    </span>
                </template>

                <biz-dropdown-scroll
                    :max-height="dropdownMaxHeight"
                    :max-width="dropdownMaxWidth"
                    @scroll="onScroll"
                >
                    <biz-dropdown-item
                        v-for="country in displayedCountries"
                        :key="country.id"
                        tag="a"
                        :is-active="(country.id == selectedCountry.id)"
                        @click="computedValue.country = country.id"
                    >
                        {{ country.value }} (+{{ country.dial }})
                    </biz-dropdown-item>
                </biz-dropdown-scroll>
            </biz-dropdown-search>
        </p>

        <div class="control is-expanded has-icons-left">
            <input
                ref="input-phonenumber"
                v-model="computedValue['number']"
                class="input"
                :class="{'is-danger': hasError}"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                @keypress="keyPress"
            >
            <span class="icon is-left">
                +{{ selectedCountry.dial ?? '' }}
            </span>
        </div>
    </div>
</template>

<script>
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizDropdownScroll from '@/Biz/DropdownScroll';
    import BizDropdownSearch from '@/Biz/DropdownSearch';
    import { useModelWrapper } from '@/Libs/utils';
    import { debounceTime } from '@/Libs/defaults';
    import { debounce, filter, find, isEmpty } from 'lodash';

    export default {
        name: 'BizPhone',

        components: {
            BizDropdownItem,
            BizDropdownScroll,
            BizDropdownSearch,
        },

        props: {
            countryOptions: { type: Array, required: true },
            defaultCountry: { type: String, default: 'US' },
            disabled: { type: Boolean, default: false },
            dropdownMaxHeight: { type: Number, default: 350 },
            dropdownMaxWidth: { type: Number, default: null },
            hasError: { type: Boolean, default: false },
            modelValue: { type: Object, default: undefined },
            optionsIncreaseNumber: { type: Number, default: 50 },
            optionsMaxNumber: { type: Number, default: 25 },
            placeholder: { type: String, default: null },
            required: { type: Boolean, default: false },
        },

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            }
        },

        data() {
            return {
                filteredCountries: this.countryOptions,
                latestIndex: this.optionsMaxNumber,
            };
        },

        computed: {
            selectedCountry() {
                return find(
                    this.countryOptions,
                    (option) => option.id == (this.computedValue.country ?? this.defaultCountry)
                );
            },

            displayedCountries() {
                return this.filteredCountries.slice(0, this.latestIndex);
            },
        },

        beforeMount() {
            if (!this.computedValue.country || this.computedValue.country == '') {
                this.computedValue.country = this.defaultCountry;
            }
        },

        methods: {
            focus() {
                this.$refs.dropdownSearch.focus()
            },

            getDefaultCountries() {
                return this.countryOptions.slice(0, this.optionsMaxNumber);
            },

            keyPress(event) {
                let char = String.fromCharCode(event.keyCode);

                const lastCharacter = event.target.value.slice(-1);

                if ((new RegExp('^[0-9]+$')).test(char)) {
                    return true;
                }

                event.preventDefault();
            },

            searchCountry: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredCountries = filter(
                        this.countryOptions,
                        function (country) {
                            return (
                                new RegExp(term, 'i').test(country.value)
                                || new RegExp(term, 'i').test(country.id)
                            );
                        });
                } else {
                    this.filteredCountries = this.countryOptions;
                }

                this.latestIndex = this.optionsMaxNumber;
            }, debounceTime),

            onScroll ({ target: { scrollTop, clientHeight, scrollHeight }}) {
                if (
                    (scrollTop + clientHeight) >= scrollHeight
                    && this.filteredCountries.length > (this.latestIndex - 1)
                ) {
                    this.latestIndex += this.optionsIncreaseNumber;
                }
            },
        },
    };
</script>
