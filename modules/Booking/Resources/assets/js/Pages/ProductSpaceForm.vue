<template>
    <biz-form-dropdown-search
        label="Select space"
        :close-on-click="true"
        @search="searchOptions($event)"
    >
        <template #trigger>
            <span
                class="has-text-left"
                :style="{'min-width': '10rem'}"
            >
                {{ selectedSpace }}
            </span>
        </template>

        <div style="max-height: 30rem; overflow-y: scroll">
            <biz-dropdown-item
                v-for="option in filteredOptions"
                :key="option.id"
                @click="space = option.id"
            >
                {{ option.value }}
            </biz-dropdown-item>
        </div>

        <template #note>
            <p class="help is-info">
                The product can only have one space.
            </p>
        </template>
    </biz-form-dropdown-search>
</template>

<script>
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import { debounceTime } from '@/Libs/defaults';
    import { find, debounce, isEmpty, filter } from 'lodash';
    import { ref, computed } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: "ProductSpaceForm",

        components: {
            BizFormDropdownSearch,
            BizDropdownItem,
        },

        props: {
            spaceOptions: { type: Array, default: () => [] },
            modelValue: { type: [String, Number, null], required: true },
        },

        setup(props, { emit }) {
            return {
                filteredOptions: ref(
                    computed(() => props.spaceOptions).value
                ),
                space: useModelWrapper(props, emit),
            };
        },

        computed: {
            selectedSpace() {
                const selectedSpace = find(this.spaceOptions, {id: this.space});

                if (selectedSpace) {
                    return selectedSpace.value;
                }

                return null;
            },
        },

        methods: {
            searchOptions: debounce(function(term) {
                if (! isEmpty(term) && term.length > 1) {
                    this.filteredOptions = filter(this.spaceOptions, function (option) {
                        const termRegex = new RegExp(term, 'i');

                        return new RegExp(term, 'i').test(option.value);
                    });
                } else {
                    this.filteredOptions = this.spaceOptions;
                }
            }, debounceTime),
        },
    }
</script>