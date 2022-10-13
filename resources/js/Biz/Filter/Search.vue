<template>
    <biz-form-field-horizontal>
        <template #label>
            Search
        </template>

        <div class="field has-addons">
            <div class="control">
                <biz-input
                    v-model="term"
                    maxlength="255"
                    @keyup.prevent="search(term)"
                />
            </div>

            <div class="control">
                <biz-button-icon
                    type="button"
                    :icon="iconClear"
                    @click="reset()"
                />
            </div>
        </div>
    </biz-form-field-horizontal>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizFormFieldHorizontal from '@/Biz/Form/FieldHorizontal';
    import BizInput from '@/Biz/Input';
    import { useModelWrapper } from '@/Libs/utils';
    import { debounceTime } from '@/Libs/defaults';
    import { debounce } from 'lodash';
    import { clear as iconClear } from '@/Libs/icon-class';

    export default {
        name: 'BizFilterSearch',

        components: {
            BizButtonIcon,
            BizFormFieldHorizontal,
            BizInput,
        },

        props: {
            modelValue: { type: String, default: '' },
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