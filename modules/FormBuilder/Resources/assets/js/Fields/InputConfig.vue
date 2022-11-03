<template>
    <div>
        <template
            v-for="(group, groupName, indexConfig) in configOptions"
            :key="indexConfig"
        >
            <biz-card
                v-if="!isBlank(entity)"
                :class="{'mb-1': indexConfig != numberOfOptions - 1}"
                :is-collapsed="true"
                :is-expanding-on-load="indexConfig == 0"
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
                        :entity="entity"
                        :settings="config.settings"
                    />

                    <component
                        :is="config.component"
                        v-else-if="config.component && groupName == 'validation'"
                        v-model="validationRules[ key ]"
                        :label="config.label"
                        :entity="entity"
                        :settings="config.settings"
                    />

                    <hr>
                </template>
            </biz-card>
        </template>
    </div>
</template>

<script>
    import BizCard from '@/Biz/Card';
    import ConfigAddOption from '@/Blocks/Configs/AddOption';
    import ConfigAutoGenerateKey from '@/Blocks/Configs/AutoGenerateKey';
    import ConfigCheckbox from '@/Blocks/Configs/Checkbox';
    import ConfigInput from '@/Blocks/Configs/Input';
    import ConfigNumber from '@/Blocks/Configs/Number';
    import ConfigRowSection from '@/Blocks/Configs/ConfigRowSection';
    import configs from './../FieldStructures/configs';
    import ConfigSelect from '@/Blocks/Configs/Select';
    import TRBL from '@/Blocks/Configs/TRBL';
    import TRBLInput from '@/Blocks/Configs/TRBLInput';
    import { camelCase } from "lodash";
    import { isBlank } from '@/Libs/utils';
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        name: 'InputConfig',

        components: {
            BizCard,
            ConfigAddOption,
            ConfigAutoGenerateKey,
            ConfigCheckbox,
            ConfigInput,
            ConfigNumber,
            ConfigRowSection,
            ConfigSelect,
            TRBL,
            TRBLInput,
        },

        props: {
            modelValue: { type: Object, required: true },
        },

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
