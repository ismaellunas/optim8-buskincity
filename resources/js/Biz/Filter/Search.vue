<template>
    <biz-form-field-horizontal>
        <template v-slot:label>
            Search
        </template>
        <div class="columns">
            <div class="column is-three-quarters">
                <biz-input
                    v-model="term"
                    maxlength="255"
                    @keyup.prevent="search(term)"
                />
            </div>
            <div class="column">
                <biz-button-icon
                    icon="fas fa-times"
                    type="button"
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

    export default {
        name: 'BizFilterSearch',

        components: {
            BizButtonIcon,
            BizFormFieldHorizontal,
            BizInput,
        },

        props: {
            modelValue: {required: true},
        },

        emits: [
            'search',
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                term: useModelWrapper(props, emit),
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
    }
</script>