<template>
    <biz-dropdown>
        <template #trigger>
            <slot name="trigger" />

            <span class="icon is-small">
                <i
                    class="fas fa-angle-down"
                    aria-hidden="true"
                />
            </span>
        </template>

        <div class="field has-addons mb-0">
            <p class="control has-icons-left is-expanded">
                <biz-input
                    v-model="term"
                    :placeholder="placeholder"
                    @keyup.prevent="$emit('search', term)"
                />
                <span class="icon is-small is-left">
                    <i class="fas fa-search" />
                </span>
            </p>
            <div class="control">
                <a
                    class="button"
                    @click="clearTerm"
                >
                    <span class="icon is-small">
                        <i class="fas fa-times" />
                    </span>
                </a>
            </div>
        </div>

        <slot />
    </biz-dropdown>
</template>

<script>
    import BizDropdown from '@/Biz/Dropdown';
    import BizInput from '@/Biz/Input';

    export default {
        name: 'BizDropdownSearch',

        components: {
            BizDropdown,
            BizInput,
        },

        props: {
            placeholder: {
                type: String,
                default: 'Search ...'
            },
        },

        emits: [
            'search',
        ],

        data() {
            return {
                term: null,
            };
        },

        methods: {
            clearTerm() {
                this.term = null;
                this.$emit('search', null);
            },
        },
    };
</script>
