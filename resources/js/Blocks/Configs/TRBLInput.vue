<template>
    <div>
        <biz-label>{{ label }}</biz-label>

        <biz-field-horizontal
            v-for="(name, key) in inputs"
            :key="key"
        >
            <template #label>
                {{ capitalize(name) }}
            </template>

            <div class="field has-addons">
                <p class="control">
                    <biz-number
                        ref="input"
                        v-model="trbl[name]"
                    />
                </p>
                <p class="control">
                    <a class="button is-static">
                        px
                    </a>
                </p>
            </div>
        </biz-field-horizontal>
    </div>
</template>

<script>
    import BizFieldHorizontal from '@/Biz/Form/FieldHorizontal';
    import BizLabel from '@/Biz/Label';
    import BizNumber from '@/Biz/Number';
    import { capitalize, keys } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizFieldHorizontal,
            BizLabel,
            BizNumber,
        },

        props: {
            modelValue: { type: Object, default: undefined },
            label: { type: String, default: "" },
        },

        emits: ['update:modelValue'],

        setup(props, {emit}) {

            if (typeof props.modelValue === "undefined") {
                emit('update:modelValue', {
                    top: null,
                    right: null,
                    bottom: null,
                    left: null,
                    unit: 'px',
                });
            }

            return {
                trbl: useModelWrapper(props, emit),
            };
        },

        computed: {
            inputs() {
                return keys(this.trbl)
                    .filter((key) => key !== 'unit');
            }
        },

        methods: {
            capitalize: capitalize,
        },
    };
</script>
