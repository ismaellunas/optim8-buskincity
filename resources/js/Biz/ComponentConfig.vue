<template>
    <biz-card class-card-content="px-1 py-2">
        <template #headerTitle>
            <h4 class="title is-4">
                {{ entity.title ?? entity.componentName }}
            </h4>
        </template>

        <template #headerButton>
            <biz-button-icon
                class="is-small"
                type="button"
                :icon="iconClose"
                @click.stop="closeConfig"
            />
        </template>

        <template
            v-for="(group, groupName, indexConfig) in configOptions"
            :key="groupName"
        >
            <component
                :is="group.component"
                v-if="group.component && !isBlank(entity.config[ groupName ])"
                :ref="`config-${indexConfig}`"
                v-model="entity.config[ groupName ]"
                class-card-content="p-3"
                :class="{'mb-1': indexConfig != numberOfOptions - 1}"
                :is-expanding-on-load="indexConfig == 0"
                :structure="computedStructure"
                @on-click-header-card="onClickHeaderCard($event, indexConfig)"
            />

            <biz-card
                v-else-if="!isBlank(entity.config[ groupName ])"
                :ref="`config-${indexConfig}`"
                class-card-content="p-3"
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
    </biz-card>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizCard from '@/Biz/Card.vue';
    import configs from '@/ComponentStructures/configs';
    import { camelCase, merge, forEach } from 'lodash';
    import { close as iconClose } from '@/Libs/icon-class';
    import { computed, reactive, onBeforeMount } from 'vue';
    import { defineAsyncComponent } from 'vue';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { usePage } from '@inertiajs/vue3';

    export default {
        components: {
            BizButtonIcon,
            BizCard,
            BizIcon: defineAsyncComponent(() => import('../Biz/Icon.vue')),
            ConfigCheckbox: defineAsyncComponent(() => import('../Blocks/Configs/Checkbox.vue')),
            ConfigCheckboxes: defineAsyncComponent(() => import('../Blocks/Configs/Checkboxes.vue')),
            ConfigColumns: defineAsyncComponent(() => import('../Blocks/Configs/ConfigColumns.vue')),
            ConfigImageBrowser: defineAsyncComponent(() => import('../Blocks/Configs/ImageBrowser.vue')),
            ConfigInput: defineAsyncComponent(() => import('../Blocks/Configs/Input.vue')),
            ConfigInputIcon: defineAsyncComponent(() => import('../Blocks/Configs/InputIcon.vue')),
            ConfigNumberAddons: defineAsyncComponent(() => import('../Blocks/Configs/NumberAddons.vue')),
            ConfigRowSection: defineAsyncComponent(() => import('../Blocks/Configs/ConfigRowSection.vue')),
            ConfigSelect: defineAsyncComponent(() => import('../Blocks/Configs/Select.vue')),
            ConfigSelectMultiple: defineAsyncComponent(() => import('../Blocks/Configs/SelectMultiple.vue')),
            TRBL: defineAsyncComponent(() => import('../Blocks/Configs/TRBL.vue')),
            TRBLInput: defineAsyncComponent(() => import('../Blocks/Configs/TRBLInput.vue')),
        },

        props: {
            modelValue: { type: Object, required: true },
            contentConfigId: { type: String, default: "" },
            structure: { type: Object, default: () => {} },
        },

        setup(props, { emit }) {
            const moduleConfigs = reactive({});

            const entity = useModelWrapper(props, emit);

            const allConfigs = computed(() => merge(configs, moduleConfigs));

            onBeforeMount(async () => {
                const promises = [];

                forEach(usePage().props.modulePageBuilderComponents, (moduleComponent) => {
                    if (moduleComponent?.in_module) {
                        promises.push(import(
                            `../../../modules/${moduleComponent.in_module}/Resources/assets/js/ComponentStructures/${moduleComponent.filename}.js`
                        ));
                    } else {
                        promises.push(import(`../ComponentStructures/modules/${moduleComponent.filename}.js`));
                    }
                });

                const components = await Promise.all(promises);

                if (components) {
                    forEach(components, (component) => {
                        moduleConfigs[camelCase(component.default.componentName)] = component.config;
                    })
                }

                const componentConfig = allConfigs.value[camelCase(entity.value.componentName)];

                for (const [groupKey, group] of Object.entries(componentConfig)) {
                    for (const [key, value] of Object.entries(group)) {
                        if (entity.value.config[groupKey] === undefined) {
                            entity.value.config[groupKey] = {};
                        }
                    }
                }
            });

            return {
                allConfigs,
                computedContentConfigId: useModelWrapper(props, emit, 'contentConfigId'),
                computedStructure: useModelWrapper(props, emit, 'structure'),
                entity,
                iconClose,
                isBlank,
                moduleConfigs,
            };
        },

        computed: {
            configOptions() {
                return this.allConfigs[ camelCase(this.entity.componentName) ];
            },

            numberOfOptions() {
                return Object.keys(this.configOptions).length;
            },
        },

        methods: {
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

            closeConfig() {
                this.computedContentConfigId = '';
            },
        }
    };
</script>
