<template>
    <biz-form-dropdown-search
        :label="i18n.select_space"
        :close-on-click="true"
        :required="required"
        :message="message"
        @search="searchOptions($event)"
    >
        <template #trigger>
            <span
                class="has-text-left"
                :style="{'min-width': '15rem'}"
            >
                {{ selectedSpaceLabel }}
            </span>
        </template>

        <div style="max-height: 30rem; overflow-y: scroll">
            <template
                v-for="option in filteredOptions"
                :key="option.id ?? 'placeholder'"
            >
                <biz-dropdown-item
                    v-if="option.id"
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
    import { computed, ref, watch } from 'vue';
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
            modelValue: { type: [String, Number, null], default: null },
            required: { type: Boolean, default: false },
            message: { type: [Array, Object, String], default: undefined },
        },

        setup(props, { emit }) {
            const pageI18n = computed(() => usePage().props.i18n ?? {});

            const options = computed(() => [
                {
                    id: null,
                    value: pageI18n.value.select ?? 'Select',
                    depth: 0,
                },
                ...props.spaceOptions,
            ]);

            const filteredOptions = ref([]);

            watch(options, (value) => {
                filteredOptions.value = value;
            }, { immediate: true });

            return {
                filteredOptions,
                i18n: pageI18n,
                options,
                space: useModelWrapper(props, emit),
            };
        },

        computed: {
            selectedSpaceLabel() {
                const selectedSpace = find(this.options, { id: this.space });

                if (selectedSpace?.id) {
                    return selectedSpace.value;
                }

                return this.i18n.select ?? 'Select';
            },
        },

        methods: {
            searchOptions: debounce(function(term) {
                if (! isEmpty(term) && term.length > 1) {
                    this.filteredOptions = filter(this.options, function (option) {
                        return option.id && new RegExp(term, 'i').test(option.value);
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
