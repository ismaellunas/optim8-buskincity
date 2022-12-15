<template>
    <biz-dropdown
        :class-button="{'is-small': isSmall}"
        :is-trigger-button="isTriggerButton"
        :is-fullwidth="isFullwidth"
    >
        <template #trigger>
            <slot name="trigger" />

            <biz-icon
                v-if="isTriggerButton"
                class="is-small"
                :icon="iconAngleDown"
            />
        </template>

        <div class="field has-addons mb-0">
            <p class="control has-icons-left is-expanded">
                <biz-input
                    ref="input"
                    v-model="term"
                    :class="{'is-small': isSmall}"
                    :placeholder="placeholder"
                    @keyup.prevent="$emit('search', term)"
                />
                <span class="icon is-small is-left">
                    <i class="fas fa-search" />
                </span>
            </p>
            <div class="control">
                <a
                    :class="{'button': true, 'is-small': isSmall}"
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
    import BizIcon from '@/Biz/Icon';
    import { angleDown as iconAngleDown, clear as iconClear } from '@/Libs/icon-class';

    export default {
        name: 'BizDropdownSearch',

        components: {
            BizDropdown,
            BizInput,
            BizIcon,
        },

        props: {
            placeholder: {
                type: String,
                default: 'Search ...'
            },

            isClearable: {
                type: Boolean,
                default: false,
            },

            isTriggerButton: {
                type: Boolean,
                default: true
            },

            isSmall: {
                type: Boolean,
                default: false
            },

            isFullwidth: {
                type: Boolean,
                default: false
            },
        },

        emits: [
            'search',
        ],


        data() {
            return {
                term: null,
                iconAngleDown,
                iconClear,
            };
        },

        methods: {
            focus() {
                this.$refs.input.focus();
            },

            clearTerm() {
                this.term = null;
                this.$emit('search', null);
            },
        },
    };
</script>
