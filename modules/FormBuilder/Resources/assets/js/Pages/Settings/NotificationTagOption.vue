<template>
    <biz-dropdown-search
        class="is-right"
        :close-on-click="true"
        :is-trigger-button="false"
        @search="searchFieldName($event)"
    >
        <template #trigger>
            <span :style="{'min-width': '4rem'}">
                <i :class="icon.bracketCurly" />
                <i :class="icon.ellipsis" />
                <i :class="icon.bracketCurlyRight" />
            </span>
        </template>

        <biz-dropdown-item
            v-for="option in filteredOptions"
            :key="option.id"
            @click="$emit('on-select-option', option.id, inputName)"
        >
            {{ option.value }}
        </biz-dropdown-item>
    </biz-dropdown-search>
</template>

<script>
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizDropdownSearch from '@/Biz/DropdownSearch';
    import icon from '@/Libs/icon-class';
    import { find, debounce, isEmpty, filter } from 'lodash';
    import { debounceTime } from '@/Libs/defaults';

    export default {
        name: 'TagOption',

        components: {
            BizDropdownItem,
            BizDropdownSearch,
        },

        props: {
            inputName: { type: String, required: true },
            options: { type: Array, default: () => [] },
        },

        emits: [
            'on-select-option',
        ],

        data() {
            return {
                icon,
                filteredOptions: this.options.slice(0, 10),
            };
        },

        methods: {
            searchFieldName: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredOptions = filter(this.options, function (fieldName) {
                        return new RegExp(term, 'i').test(fieldName.value);
                    }).slice(0, 10);
                } else {
                    this.filteredOptions = this.options.slice(0, 10);
                }
            }, debounceTime),
        },
    }
</script>