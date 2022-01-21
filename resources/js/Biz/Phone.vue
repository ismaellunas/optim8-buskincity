<template>
    <div class="field has-addons">
        <p class="control">
            <biz-dropdown-search
                label="Country"
                :close-on-click="true"
                @search="searchCountry($event)"
            >
                <template #trigger>
                    <span class="icon is-small">
                        {{ selectedCountry.id }}
                    </span>
                </template>

                <biz-dropdown-scroll :max-height="350">
                    <biz-dropdown-item
                        v-for="country in filteredCountries"
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
    import { debounce, filter, find, isEmpty } from 'lodash';

    export default {
        name: 'BizPhone',

        components: {
            BizDropdownItem,
            BizDropdownScroll,
            BizDropdownSearch,
        },

        props: {
            hasError: { type: Boolean, default: false },
            modelValue: { type: [Object], default: undefined },
            countryOptions: { type: Array, required: true },
            defaultCountry: { type: String, default: 'US' },
        },

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            }
        },

        data() {
            return {
                filteredCountries: this.countryOptions,
            };
        },

        computed: {
            selectedCountry() {
                return find(
                    this.countryOptions,
                    (option) => option.id == this.computedValue.country
                );
            },
        },

        created() {
            if (!this.computedValue.country || this.computedValue.country == '') {
                this.computedValue.country = this.defaultCountry;
            }
        },

        methods: {
            focus() {
                this.$refs.input.focus()
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
            }, 750),
        },
    }
</script>
