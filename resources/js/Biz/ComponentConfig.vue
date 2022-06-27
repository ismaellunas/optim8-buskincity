<template>
    <div>
        <template
            v-for="(group, groupName, indexConfig) in configOptions"
            :key="groupName"
        >
            <component
                :is="group.component"
                v-if="group.component && !isBlank(entity.config[ groupName ])"
                v-model="entity.config[ groupName ]"
                :class="{'mb-1': indexConfig != numberOfOptions - 1}"
            />

            <card
                v-else-if="!isBlank(entity.config[ groupName ])"
                :class="{'mb-1': indexConfig != numberOfOptions - 1}"
            >
                <template #headerTitle>
                    {{ group.label }}
                </template>

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
                        :settings="config.settings"
                    />

                    <hr>
                </template>
            </card>
        </template>
    </div>
</template>

<script>
    import BizCheckbox from '@/Biz/Checkbox';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import Card from '@/Biz/Card';
    import Checkboxes from '@/Blocks/Configs/Checkboxes';
    import ConfigRowSection from '@/Blocks/Configs/ConfigRowSection';
    import SelectMultiple from '@/Blocks/Configs/SelectMultiple';
    import TRBL from '@/Blocks/Configs/TRBL';
    import TRBLInput from '@/Blocks/Configs/TRBLInput';
    import configs from '@/ComponentStructures/configs';
    import { camelCase } from "lodash";
    import { isBlank } from '@/Libs/utils';
    import { useModelWrapper } from '@/Libs/utils'

    export default {

        components: {
            BizCheckbox,
            BizFormInput,
            BizFormSelect,
            Card,
            Checkboxes,
            ConfigRowSection,
            SelectMultiple,
            TRBL,
            TRBLInput,
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
            },

            numberOfOptions() {
                return Object.keys(this.configOptions).length;
            },
        },

        methods: {
            isBlank: isBlank,
        }
    };
</script>
