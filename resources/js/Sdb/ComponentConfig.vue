<template>
    <div>
        <template v-for="(config, key) in configOptions">
            <sdb-form-select
                v-if="config.type === 'select'"
                :label="config.label"
                v-model="entity.config[ key ]"
            >
                <option
                    v-for="option in config.options"
                    :value="option.value">
                    {{ option.name }}
                </option>
            </sdb-form-select>
        </template>
    </div>
</template>

<script>
    import SdbFormSelect from '@/Sdb/Form/Select';
    import configs from '@/ComponentStructures/configs';
    import { isBlank } from '@/Libs/utils';
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        components: {
            SdbFormSelect,
        },
        props: ['modelValue'],
        setup(props, { emit }) {
            return {
                entity: useModelWrapper(props, emit),
            };
        },
        computed: {
            configOptions() {
                return configs[ this.entity.componentName.toLowerCase() ];
            }
        },
    }
</script>
