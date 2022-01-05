<template>
    <div>
        <div
            v-for="(group, groupName) in configOptions"
            :key="groupName"
            class="card"
        >
            <header class="card-header">
                <p class="card-header-title">
                    {{ group.label }}
                </p>
                <!--
                <button class="card-header-icon" aria-label="more options">
                    <span class="icon">
                        <i class="fas fa-angle-down" aria-hidden="true"></i>
                    </span>
                </button>
                -->
            </header>
            <div class="card-content">
                <div class="content">
                    <template
                        v-for="(config, key) in group.config"
                        :key="key"
                    >
                        <sdb-form-select
                            v-if="config.type === 'select'"
                            v-model="entity.config[ groupName ][ key ]"
                            :label="config.label"
                        >
                            <option
                                v-for="(option, index) in config.options"
                                :key="index"
                                :value="option.value"
                            >
                                {{ option.name }}
                            </option>
                        </sdb-form-select>

                        <sdb-form-input
                            v-else-if="config.type === 'input'"
                            v-model="entity.config[ groupName ][ key ]"
                            :label="config.label"
                        />

                        <sdb-checkbox
                            v-else-if="config.type === 'checkbox'"
                            v-model:checked="entity.config[ groupName ][ key ]"
                            :value="true"
                            class="mb-2"
                        >
                            <span class="ml-2">
                                {{ config.label }}
                            </span>
                        </sdb-checkbox>

                        <component
                            :is="config.component"
                            v-else-if="config.component"
                            v-model="entity.config[ groupName ][ key ]"
                            :label="config.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import SdbCheckbox from '@/Sdb/Checkbox';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import TRBL from '@/Blocks/Configs/TRBL';
    import configs from '@/ComponentStructures/configs';
    import { camelCase } from "lodash";
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        components: {
            SdbCheckbox,
            SdbFormInput,
            SdbFormSelect,
            TRBL,
        },
        props: ['modelValue'],
        setup(props, { emit }) {
            const entity = useModelWrapper(props, emit);

            let componentConfig = configs[ camelCase(entity.value.componentName) ];

            for (const [groupKey, group] of Object.entries(componentConfig)) {
                for (const [key, value] of Object.entries(group)) {
                    if (entity.value.config[groupKey] === undefined) {
                        entity.value.config[groupKey] = {};
                    }
                }
            }

            return {
                entity,
            };
        },
        computed: {
            configOptions() {
                return configs[ camelCase(this.entity.componentName) ];
            }
        },
    }
</script>
