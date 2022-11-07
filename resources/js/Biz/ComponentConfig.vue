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
            </biz-card>
        </template>
    </div>
</template>

<script>
    import icon from '@/Libs/icon-class';
    import BizCard from '@/Biz/Card';
    import BizIcon from '@/Biz/Icon';
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
    import { camelCase, merge, forEach } from "lodash";
    import { isBlank } from '@/Libs/utils';
    import { useModelWrapper } from '@/Libs/utils'

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
            ConfigSelect,
            ConfigSelectMultiple,
            TRBL,
            TRBLInput,
        },

        props: {
            modelValue: { type: Object, required: true },
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
            };
        },

        data() {
            return {
                icon,
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
