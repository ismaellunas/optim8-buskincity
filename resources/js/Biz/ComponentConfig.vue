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
                        <biz-form-select
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
                        </biz-form-select>

                        <biz-form-input
                            v-else-if="config.type === 'input'"
                            v-model="entity.config[ groupName ][ key ]"
                            :label="config.label"
                        />

                        <biz-checkbox
                            v-else-if="config.type === 'checkbox'"
                            v-model:checked="entity.config[ groupName ][ key ]"
                            :value="true"
                            class="mb-2"
                        >
                            <span class="ml-2">
                                {{ config.label }}
                            </span>
                        </biz-checkbox>

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
    import BizCheckbox from '@/Biz/Checkbox';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import TRBL from '@/Blocks/Configs/TRBL';
    import configs from '@/ComponentStructures/configs';
    import { camelCase } from "lodash";
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        components: {
            BizCheckbox,
            BizFormInput,
            BizFormSelect,
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
