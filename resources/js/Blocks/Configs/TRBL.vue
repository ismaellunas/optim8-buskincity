<template>
    <div>
        <biz-label class="is-size-7">{{ label }}</biz-label>

        <biz-field-horizontal
            v-for="(value, key) in trbl"
            :key="key"
            field-label-class="is-size-7"
        >
            <template #label>
                {{ capitalize(key) }}
            </template>

            <biz-select
                v-model="trbl[key]"
                class="is-small is-fullwidth"
            >
                <option
                    v-for="option in options"
                    :key="option.name"
                    :value="option.value"
                >
                    {{ option.name }}
                </option>
            </biz-select>
        </biz-field-horizontal>
    </div>
</template>

<script>
    import BizFieldHorizontal from '@/Biz/Form/FieldHorizontal.vue';
    import BizLabel from '@/Biz/Label.vue';
    import BizSelect from '@/Biz/Select.vue';
    import { capitalize } from 'lodash';
    import { defaultOption, suffixNumbers } from '@/ComponentStructures/style-options';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizFieldHorizontal,
            BizLabel,
            BizSelect,
        },
        props: {
            modelValue: { type: Object, required: true },
            label: { type: String, default: null },
        },
        emits: ['update:modelValue'],
        setup(props, {emit}) {

            if (typeof props.modelValue === "undefined") {
                emit('update:modelValue', {
                    top: null,
                    right: null,
                    bottom: null,
                    left: null,
                });
            }

            return {
                trbl: useModelWrapper(props, emit),
            };
        },
        computed: {
            options() {
                return defaultOption.concat(suffixNumbers);
            }
        },
        methods: {
            capitalize: capitalize,
        },
    }
</script>
