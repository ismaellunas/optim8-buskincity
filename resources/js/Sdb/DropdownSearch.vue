<template>
    <sdb-dropdown>
        <template #trigger>
            <slot name="trigger" />

            <span class="icon is-small">
                <i
                    class="fas fa-angle-down"
                    aria-hidden="true"
                />
            </span>
        </template>

        <div class="field has-addons">
            <p class="control has-icons-left">
                <sdb-input
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
    </sdb-dropdown>
</template>

<script>
    import SdbDropdown from '@/Sdb/Dropdown';
    import SdbInput from '@/Sdb/Input';

    export default {
        name: 'SdbDropdownSearch',

        components: {
            SdbDropdown,
            SdbInput,
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
