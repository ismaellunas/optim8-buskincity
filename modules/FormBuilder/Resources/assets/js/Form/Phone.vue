<template>
    <slot
        :computed-value="computedValue"
        :countries="displayedCountries"
        :country-id="selectedCountryId"
        :dial="selectedCountry.dial ?? '1'"
        :is-active="isActive"
        :set-ref-dropdown-menu="setRefDropdownMenu"
        :set-ref-trigger="setRefTrigger"
        :term="term"
        :handlers="{ clearTermOrClose, numberOnly, onScroll, search, selectCountry, toggleMenu }"
    />
</template>

<script>
    import { debounce, filter, find, isEmpty } from 'lodash';
    import { debounceTime } from '@/Libs/defaults';
    import { ref, computed } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormPhone',

        props: {
            modelValue: {type: Object, required: true},
            defaultCountry: {type: String, default: 'US'},
            countryOptions: {type: Array, default: () => []},
            optionsMaxNumber: { type: Number, default: 25 },
            optionsIncreaseNumber: { type: Number, default: 50 },
        },

        setup(props, { emit }) {
            const filteredCountries = ref(props.countryOptions);
            const isActive = ref(false);
            const latestIndex = ref(props.optionsMaxNumber);
            const refDropdownMenu = ref();
            const refTrigger = ref();
            const selectedCountryId = ref(props.defaultCountry);
            const term = ref('');
            const displayedCountries = computed(() => filteredCountries.value.slice(0, latestIndex.value));
            const selectedCountry = computed(() => find(
                props.countryOptions,
                (option) => option.id == (selectedCountryId.value ?? props.defaultCountry)
            ));

            return {
                computedValue: useModelWrapper(props, emit),
                isActive,
                refTrigger,
                refDropdownMenu,
                term,
                filteredCountries,
                latestIndex,
                selectedCountry,
                selectedCountryId,
                displayedCountries,
            };
        },

        created() {
            if (typeof window !== 'undefined') {
                document.addEventListener('click', this.clickedOutside);
                document.addEventListener('keyup', this.keyPress);
            }
        },

        beforeUnmount() {
            if (typeof window !== 'undefined') {
                document.removeEventListener('click', this.clickedOutside);
                document.removeEventListener('keyup', this.keyPress);
            }
        },

        methods: {
            toggleMenu() {
                this.isActive = !this.isActive;
                if (this.isActive) {
                    setTimeout(
                        () => this.refDropdownMenu.querySelector('input[type=text]').focus(),
                        150
                    );
                }
            },

            clearTermOrClose(){
                if (this.term != '') {
                    this.term = '';
                    this.search(this.term);
                } else {
                    this.isActive = false;
                }
            },

            search: debounce(function(term) {
                this.term = term;

                if (!isEmpty(term) && term.length > 1) {
                    this.filteredCountries = filter(
                        this.countryOptions,
                        (country) => (
                            [country.value, country.id, "+"+country.dial]
                                .join(" ")
                                .toLowerCase()
                                .indexOf(term.toLowerCase())
                        ) !== -1
                    );
                } else {
                    this.filteredCountries = this.countryOptions;
                }

                this.latestIndex = this.optionsMaxNumber;
            }, debounceTime),

            setRefTrigger(el) {
                this.refTrigger = el;
            },

            setRefDropdownMenu(el) {
                this.refDropdownMenu = el;
            },

            selectCountry(country) {
                this.computedValue.country = country.id;
                this.selectedCountryId = country.id;
                this.isActive = false;
            },

            onScroll({ target: { scrollTop, clientHeight, scrollHeight }}) {
                if (
                    (scrollTop + clientHeight) >= scrollHeight
                    && this.filteredCountries.length > (this.latestIndex - 1)
                ) {
                    this.latestIndex += this.optionsIncreaseNumber;
                }
            },

            numberOnly(event) {
                let char = String.fromCharCode(event.keyCode);

                const lastCharacter = event.target.value.slice(-1);

                if ((new RegExp('^[0-9]+$')).test(char)) {
                    return true;
                }

                event.preventDefault();
            },

            isInWhiteList(el) {
                if (el === this.refDropdownMenu) return true
                if (el === this.refTrigger) return true

                // All chidren from dropdown
                if (
                    this.refDropdownMenu !== undefined
                    && this.refDropdownMenu !== null
                ) {
                    const children = this.refDropdownMenu.querySelectorAll('*')
                    for (const child of children) {
                        if (el === child) {
                            return true;
                        }
                    }
                }
                // All children from trigger
                if (
                    this.refTrigger !== undefined
                    && this.refTrigger !== null
                ) {
                    const children = this.refTrigger.querySelectorAll('*')
                    for (const child of children) {
                        if (el === child) {
                            return true;
                        }
                    }
                }

                return false;
            },

            clickedOutside(event) {
                const target = event.target;
                if (! this.isInWhiteList(target)) {
                    this.isActive = false;
                }
            },

            keyPress({ key }) {
                if (this.isActive && (key === 'Escape' || key === 'Esc')) {
                    this.isActive = false;
                }
            },
        },
    };
</script>
