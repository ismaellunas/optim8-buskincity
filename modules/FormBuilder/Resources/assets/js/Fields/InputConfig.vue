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
                    <component
                        :is="config.component"
                        v-if="config.component && groupName != 'validation'"
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
    import Card from '@/Biz/Card';
    import ConfigCheckbox from '@/Blocks/Configs/Checkbox';
    import ConfigInput from '@/Blocks/Configs/Input';
    import ConfigNumber from '@/Blocks/Configs/Number';
    import ConfigRowSection from '@/Blocks/Configs/ConfigRowSection';
    import ConfigSelect from '@/Blocks/Configs/Select';
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
            Card,
            ConfigCheckbox,
            ConfigInput,
            ConfigNumber,
            ConfigRowSection,
            ConfigSelect,
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
