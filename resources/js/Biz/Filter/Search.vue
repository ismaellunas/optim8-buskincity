<template>
    <biz-field class="has-addons">
        <div class="control is-expanded">
            <biz-input
                v-model="term"
                :maxlength="maxlength"
                :placeholder="placeholder"
                @keyup.prevent="search(term)"
            />
        </div>

        <div class="control">
            <biz-button-icon
                type="button"
                :icon="iconClear"
                :disabled="!clearable"
                @click="reset()"
            />
        </div>
    </biz-field>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizField from '@/Biz/Field.vue';
    import BizInput from '@/Biz/Input.vue';
    import { clear as iconClear } from '@/Libs/icon-class';
    import { debounce, isEmpty } from 'lodash';
    import { debounceTime } from '@/Libs/defaults';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFilterSearch',

        components: {
            BizButtonIcon,
            BizField,
            BizInput,
        },

        props: {
            modelValue: { type: String, default: '' },
            maxlength: { type: Number, default: 255 },
            placeholder: { type: String, default: 'Search ...' },
        },

        emits: [
            'search',
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                term: useModelWrapper(props, emit),
                iconClear,
            };
        },

        computed: {
            clearable() {
                return !isEmpty(this.term);
            },
        },

        methods: {
            search: debounce(function(term = '') {
                if (term.length > 2 || term.length == 0) {
                    this.$emit('search', term);
                }
            }, debounceTime),

            reset() {
                this.term = '';
                this.search();
            },
        },
    };
</script>