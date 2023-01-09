<template>
    <div>
        <template
            v-for="(group, groupName, indexConfig) in configOptions"
            :key="indexConfig"
        >
            <component
                :is="group.component"
                v-if="group.component && !isBlank(entity)"
                :ref="`config-${indexConfig}`"
                v-model="entity"
                :class="{'mb-1': indexConfig != numberOfOptions - 1}"
                :is-expanding-on-load="indexConfig == 0"
                @on-click-header-card="onClickHeaderCard($event, indexConfig)"
            />

            <biz-card
                v-else-if="!isBlank(entity)"
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

                    <hr
                        v-if="index != (Object.keys(group.config).length - 1)"
                    >
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
    import ConfigCheckboxes from '@/Blocks/Configs/Checkboxes';
    import ConfigFileUploadAttribute from './Configs/FileUploadAttribute';
    import ConfigInput from '@/Blocks/Configs/Input';
    import ConfigNumber from '@/Blocks/Configs/Number';
    import ConfigRowSection from '@/Blocks/Configs/ConfigRowSection';
    import configs from './../FieldStructures/configs';
    import ConfigSelect from '@/Blocks/Configs/Select';
    import TRBL from '@/Blocks/Configs/TRBL';
    import TRBLInput from '@/Blocks/Configs/TRBLInput';
    import { camelCase, forEach } from "lodash";
    import { isBlank } from '@/Libs/utils';
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        name: 'InputConfig',

        components: {
            BizCard,
            ConfigAddOption,
            ConfigAutoGenerateKey,
            ConfigCheckbox,
            ConfigCheckboxes,
            ConfigFileUploadAttribute,
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

            onClickHeaderCard(isContentShown, index) {
                const self = this;

                if (isContentShown) {
                    let i = 0;

                    forEach(self.configOptions, function (group) {
                        if (i != index) {
                            if (!group.component) {
                                self.$refs[`config-${i}`][0].isContentShown = false;
                            } else {
                                let componentCard = self.$refs[`config-${i}`][0].$refs['card'];

                                if (
                                    typeof componentCard !== 'undefined'
                                    && componentCard !== null
                                ) {
                                    componentCard.isContentShown = false;
                                }
                            }
                        }

                        i++;
                    });
                }
            },
        }
    };
</script>
