<template>
    <div>
        <template
            v-for="(group, groupName, indexConfig) in configOptions"
            :key="groupName"
        >
            <component
                :is="group.component"
                v-if="group.component && !isBlank(entity.config[ groupName ])"
                :ref="`config-${indexConfig}`"
                v-model="entity.config[ groupName ]"
                :class="{'mb-1': indexConfig != numberOfOptions - 1}"
                :is-expanding-on-load="indexConfig == 0"
                :structure="computedStructure"
                @on-click-header-card="onClickHeaderCard($event, indexConfig)"
            />

            <biz-card
                v-else-if="!isBlank(entity.config[ groupName ])"
                :ref="`config-${indexConfig}`"
                :class="{'mb-1': indexConfig != numberOfOptions - 1}"
                :is-collapsed="true"
                :is-expanding-on-load="indexConfig == 0"
                @on-click-header-card="onClickHeaderCard($event, indexConfig)"
            >
                <template #headerTitle>
                    {{ group.label }}
                </template>

                <template
                    v-for="(config, key, index) in group.config"
                    :key="key"
                >
                    <component
                        :is="config.component"
                        v-model="entity.config[ groupName ][ key ]"
                        :label="config.label"
                        :settings="config.settings"
                    />

                    <hr
                        v-if="index != (Object.keys(group.config).length - 1)"
                    >
                </template>
            </biz-card>
        </template>
    </div>
</template>

<script>
    import BizCard from '@/Biz/Card.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import ConfigCheckbox from '@/Blocks/Configs/Checkbox.vue';
    import ConfigCheckboxes from '@/Blocks/Configs/Checkboxes.vue';
    import ConfigColumns from '@/Blocks/Configs/ConfigColumns.vue';
    import ConfigImageBrowser from '@/Blocks/Configs/ImageBrowser.vue';
    import ConfigInput from '@/Blocks/Configs/Input.vue';
    import ConfigInputIcon from '@/Blocks/Configs/InputIcon.vue';
    import ConfigNumberAddons from '@/Blocks/Configs/NumberAddons.vue';
    import ConfigRowSection from '@/Blocks/Configs/ConfigRowSection.vue';
    import ConfigSelect from '@/Blocks/Configs/Select.vue';
    import ConfigSelectMultiple from '@/Blocks/Configs/SelectMultiple.vue';
    import TRBL from '@/Blocks/Configs/TRBL.vue';
    import TRBLInput from '@/Blocks/Configs/TRBLInput.vue';
    import configs from '@/ComponentStructures/configs';
    import moduleConfigs from '@/Modules/ComponentStructures/configs';
    import { camelCase, merge, forEach } from 'lodash';
    import { isBlank } from '@/Libs/utils';
    import { useModelWrapper } from '@/Libs/utils';

    export default {

        components: {
            BizCard,
            BizIcon,
            ConfigCheckbox,
            ConfigCheckboxes,
            ConfigImageBrowser,
            ConfigInput,
            ConfigInputIcon,
            ConfigNumberAddons,
            ConfigRowSection,
            ConfigColumns,
            ConfigSelect,
            ConfigSelectMultiple,
            TRBL,
            TRBLInput,
        },

        props: {
            modelValue: { type: Object, required: true },
            structure: { type: Object, default: () => {} },
        },

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
                computedStructure: useModelWrapper(props, emit, 'structure'),
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

            onClickHeaderCard(isContentShown, index) {
                const self = this;

                if (isContentShown) {
                    let i = 0;

                    forEach(self.configOptions, function (group) {
                        if (i != index) {
                            if (!group.component) {
                                self.$refs[`config-${i}`][0].isContentShown = false;
                            } else {
                                self.$refs[`config-${i}`][0].$refs['card'].isContentShown = false;
                            }
                        }

                        i++;
                    });
                }
            },
        }
    };
</script>
