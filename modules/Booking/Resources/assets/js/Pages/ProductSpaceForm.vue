<template>
    <biz-form-dropdown-search
        :label="i18n.select_space"
        :close-on-click="true"
        @search="searchOptions($event)"
    >
        <template #trigger>
            <span
                class="has-text-left"
                :style="{'min-width': '15rem'}"
            >
                {{ selectedSpace }}
            </span>
        </template>

        <div style="max-height: 30rem; overflow-y: scroll">
            <template
                v-for="option in filteredOptions"
                :key="option.id"
            >
                <biz-dropdown-item
                    :is-active="option.id == space"
                    :disabled="option.is_disabled"
                    @click="selectSpace(option.id, option.is_disabled)"
                >
                    {{ dashedName(option) }}

                    <p
                        v-if="option.note"
                        class="is-size-7 is-italic"
                    >
                        *{{ option.note }}
                    </p>
                </biz-dropdown-item>
            </template>
        </div>

        <template #note>
            <p class="help is-info">
                {{ i18n.select_space_note }}
            </p>
        </template>
    </biz-form-dropdown-search>
</template>

<script>
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import { debounceTime } from '@/Libs/defaults';
    import { find, debounce, isEmpty, filter, repeat } from 'lodash';
    import { ref, computed } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { usePage } from '@inertiajs/vue3';

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
            const i18n = computed(() => usePage().props.i18n).value;
            const options = [{ id: null, value: i18n.select, depth: 0 }]
                .concat(computed(() => props.spaceOptions).value);

            return {
                options: options,
                filteredOptions: ref(options),
                space: useModelWrapper(props, emit),
                i18n: i18n,
            };
        },

        computed: {
            selectedSpace() {
                const selectedSpace = find(this.options, {id: this.space});

                if (selectedSpace) {
                    return selectedSpace.value;
                }

                return null;
            },
        },

        methods: {
            searchOptions: debounce(function(term) {
                if (! isEmpty(term) && term.length > 1) {
                    this.filteredOptions = filter(this.options, function (option) {
                        const termRegex = new RegExp(term, 'i');

                        return new RegExp(term, 'i').test(option.value);
                    });
                } else {
                    this.filteredOptions = this.options;
                }
            }, debounceTime),

            dashedName(space) {
                return repeat('—', space.depth) + ' ' + space.value;
            },

            selectSpace(spaceId, isDisabled) {
                if (! isDisabled) {
                    this.space = spaceId;
                }
            },
        },
    }
</script>