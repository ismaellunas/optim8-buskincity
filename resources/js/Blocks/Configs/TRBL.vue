<template>
    <div>
        <sdb-label>{{ label }}</sdb-label>

        <sdb-field-horizontal v-for="(value, key) in trbl">
            <template #label>{{ capitalize(key)}}</template>
            <sdb-select v-model="trbl[key]">
                <option v-for="option in options" :value="option.value">
                    {{ option.name }}
                </option>
            </sdb-select>
        </sdb-field-horizontal>
    </div>
</template>

<script>
    import SdbFieldHorizontal from '@/Sdb/Form/FieldHorizontal';
    import SdbLabel from '@/Sdb/Label';
    import SdbSelect from '@/Sdb/Select';
    import { capitalize } from 'lodash';
    import { defaultOption, suffixNumbers } from '@/ComponentStructures/style-options';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            SdbFieldHorizontal,
            SdbLabel,
            SdbSelect,
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
