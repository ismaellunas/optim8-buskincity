<template>
    <div>
        <template
            v-for="(group, groupName, indexConfig) in configOptions"
            :key="groupName"
        >
            <component
                :is="group.component"
                v-if="group.component && !isBlank(entity)"
                v-model="entity"
                :class="{'mb-1': indexConfig != numberOfOptions - 1}"
            />

            <card
                v-else-if="!isBlank(entity)"
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
                        v-model="entity[ key ]"
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
                        v-model="entity[ key ]"
                        :label="config.label"
                    />

                    <biz-checkbox
                        v-else-if="config.type === 'checkbox'"
                        v-model:checked="entity[ key ]"
                        :value="true"
                        class="mb-2"
                    >
                        <span class="ml-2">
                            {{ config.label }}
                        </span>
                    </biz-checkbox>

                    <component
                        :is="config.component"
                        v-else-if="config.component && groupName != 'validation'"
                        v-model="entity[ key ]"
                        :label="config.label"
                        :settings="config.settings"
                    />

                    <component
                        :is="config.component"
                        v-else-if="config.component && groupName == 'validation'"
                        v-model="validationRules[ key ]"
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
    import AddOption from '@/Blocks/Configs/AddOption';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import Card from '@/Biz/Card';
    import Checkboxes from '@/Blocks/Configs/Checkboxes';
    import ConfigCheckbox from '@/Blocks/Configs/Checkbox';
    import ConfigInput from '@/Blocks/Configs/Input';
    import ConfigNumber from '@/Blocks/Configs/Number';
    import ConfigRowSection from '@/Blocks/Configs/ConfigRowSection';
    import configs from './../FieldStructures/configs';
    import InputIcon from '@/Blocks/Configs/InputIcon';
    import NumberAddons from '@/Blocks/Configs/NumberAddons';
    import SelectMultiple from '@/Blocks/Configs/SelectMultiple';
    import TRBL from '@/Blocks/Configs/TRBL';
    import TRBLInput from '@/Blocks/Configs/TRBLInput';
    import { camelCase } from "lodash";
    import { isBlank } from '@/Libs/utils';
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        name: 'InputConfig',

        components: {
            AddOption,
            BizCheckbox,
            BizFormInput,
            BizFormSelect,
            Card,
            Checkboxes,
            ConfigCheckbox,
            ConfigInput,
            ConfigNumber,
            ConfigRowSection,
            InputIcon,
            NumberAddons,
            SelectMultiple,
            TRBL,
            TRBLInput,
        },

        props: ['modelValue'],

        setup(props, { emit }) {
            let entity = useModelWrapper(props, emit);

            let componentConfig = configs[ camelCase(entity.value.type) ];

            for (const [groupKey, group] of Object.entries(componentConfig)) {
                for (const [key, value] of Object.entries(group)) {
                    if (entity.value[groupKey] === undefined) {
                        entity.value[groupKey] = {};
                    }
                }
            }

            return {
                entity,
            };
        },

        computed: {
            configOptions() {
                return configs[ camelCase(this.entity.type) ];
            },

            numberOfOptions() {
                return Object.keys(this.configOptions).length;
            },

            validationRules() {
                return this.entity.validation.rules ?? {};
            },
        },

        methods: {
            isBlank: isBlank,
        }
    };
</script>
