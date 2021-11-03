<template>
    <sdb-form-field-horizontal>
        <template v-slot:label>
            Search
        </template>
        <div class="columns">
            <div class="column is-three-quarters">
                <sdb-input
                    v-model="term"
                    maxlength="255"
                    @keyup.prevent="search(term)"
                />
            </div>
            <div class="column">
                <sdb-button-icon
                    icon="fas fa-times"
                    type="button"
                    @click="reset()"
                />
            </div>
        </div>
    </sdb-form-field-horizontal>
</template>

<script>
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbFormFieldHorizontal from '@/Sdb/Form/FieldHorizontal';
    import SdbInput from '@/Sdb/Input';
    import { useModelWrapper } from '@/Libs/utils';
    import { debounce } from 'lodash';

    export default {
        name: 'SdbFilterSearch',

        components: {
            SdbButtonIcon,
            SdbFormFieldHorizontal,
            SdbInput,
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
            }, 1000),

            reset() {
                this.term = '';
                this.search();
            },
        },
    }
</script>