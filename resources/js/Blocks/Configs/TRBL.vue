<template>
    <div>
        <biz-label>{{ label }}</biz-label>

        <biz-field-horizontal v-for="(value, key) in trbl">
            <template #label>{{ capitalize(key)}}</template>
            <biz-select v-model="trbl[key]">
                <option v-for="option in options" :value="option.value">
                    {{ option.name }}
                </option>
            </biz-select>
        </biz-field-horizontal>
    </div>
</template>

<script>
    import BizFieldHorizontal from '@/Biz/Form/FieldHorizontal';
    import BizLabel from '@/Biz/Label';
    import BizSelect from '@/Biz/Select';
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
            modelValue: {},
            label: String,
        },
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
        methods: {
            capitalize: capitalize,
        },
        computed: {
            options() {
                return defaultOption.concat(suffixNumbers);
            }
        }
    }
</script>
