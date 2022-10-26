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
                    <component
                        :is="config.component"
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
    import Card from '@/Biz/Card';
    import ConfigCheckbox from '@/Blocks/Configs/Checkbox';
    import ConfigCheckboxes from '@/Blocks/Configs/Checkboxes';
    import ConfigImageBrowser from '@/Blocks/Configs/ImageBrowser';
    import ConfigInput from '@/Blocks/Configs/Input';
    import ConfigInputIcon from '@/Blocks/Configs/InputIcon';
    import ConfigNumberAddons from '@/Blocks/Configs/NumberAddons';
    import ConfigRowSection from '@/Blocks/Configs/ConfigRowSection';
    import configs from '@/ComponentStructures/configs';
    import ConfigSelect from '@/Blocks/Configs/Select';
    import ConfigSelectMultiple from '@/Blocks/Configs/SelectMultiple';
    import moduleConfigs from '@/Modules/ComponentStructures/configs';
    import TRBL from '@/Blocks/Configs/TRBL';
    import TRBLInput from '@/Blocks/Configs/TRBLInput';
    import { camelCase, merge } from "lodash";
    import { isBlank } from '@/Libs/utils';
    import { useModelWrapper } from '@/Libs/utils'

    export default {

        components: {
            Card,
            ConfigCheckbox,
            ConfigCheckboxes,
            ConfigImageBrowser,
            ConfigInput,
            ConfigInputIcon,
            ConfigNumberAddons,
            ConfigRowSection,
            ConfigSelect,
            ConfigSelectMultiple,
            TRBL,
            TRBLInput,
        },

        props: ['modelValue'],

        setup(props, { emit }) {
            const entity = useModelWrapper(props, emit);

            let allConfig = merge(configs, moduleConfigs);
            let componentConfig = allConfig[ camelCase(entity.value.componentName) ];

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
                let componentConfigs = merge(configs, moduleConfigs);
                return componentConfigs[ camelCase(this.entity.componentName) ];
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
