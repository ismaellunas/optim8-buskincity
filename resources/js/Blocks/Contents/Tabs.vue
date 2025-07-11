<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <biz-button
            type="button"
            class="is-small mb-2"
            @click="addTabs()"
        >
            Add Tabs
        </biz-button>

        <div
            class="tabs"
            :class="tabClass"
        >
            <ul>
                <li
                    v-for="(tab, index) in entity.content.tabs"
                    :key="index"
                    class="border-dash pt-3 pr-3"
                    :class="{ 'is-active': selectedIndex === index }"
                >
                    <biz-toolbar-content
                        v-if="totalTabs > 1"
                        :can-duplicate="false"
                        :can-move="false"
                        style="z-index: 1"
                        @delete-content="deleteTabs(index)"
                    />
                    <a @click="selectedIndex = index">
                        <span
                            v-if="tab.icon !== null"
                            class="icon is-small"
                            @click="openModal()"
                        >
                            <i
                                :class="tab.icon"
                                aria-hidden="true"
                            />
                        </span>
                        <span
                            v-else
                            class="icon is-small"
                            @click="openModal()"
                        >
                            <i
                                class="empty-icon"
                                aria-hidden="true"
                            />
                        </span>
                        <span
                            class="input-area"
                            contenteditable
                            @blur="onEditTabName($event, index)"
                            v-text="tab.name"
                        />
                    </a>
                </li>
            </ul>
        </div>

        <template
            v-for="(tab, index) in entity.content.tabs"
            :key="index"
        >
            <div
                v-if="selectedIndex === index"
                class="content border-dash"
            >
                <biz-form-text-editor-full-inline
                    v-model="tab.html"
                />
            </div>
        </template>

        <biz-icon-browser
            v-if="isModalOpen"
            :can-remove="true"
            :has-type="true"
            :icon-classes="iconClasses"
            @close="closeModal()"
            @on-selected-icon="onSelectedIcon"
            @remove-icon="removeIcon"
        />
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import MixinHasModal from '@/Mixins/HasModal';
    import fontawesomeAllClasses from '@/Json/fontawesome-all-classes.json';
    import BizButton from '@/Biz/Button.vue';
    import BizFormTextEditorFullInline from '@/Biz/Form/TextEditorFullInline.vue';
    import BizIconBrowser from '@/Biz/Modal/IconBrowser.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import { cloneDeep, concat } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';
    import { confirmDelete } from '@/Libs/alert';

    export default {
        name: 'ContentTabs',

        components: {
            BizButton,
            BizFormTextEditorFullInline,
            BizIconBrowser,
            BizToolbarContent,
        },

        mixins: [
            MixinContentHasDimension,
            MixinDeletableContent,
            MixinDuplicableContent,
            MixinHasModal,
        ],

        props: {
            id: {type: String, default: null},
            modelValue: {type: Object, required: true},
        },

        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
                iconClasses: fontawesomeAllClasses,
            };
        },

        data() {
            return {
                selectedIndex: (
                    this.entity.content.tabs.length > 0 ? 0 : null
                ),
            };
        },

        computed: {
            totalTabs() {
                return this.entity.content.tabs.length;
            },

            tabClass() {
                return concat(
                    (this.config.tabs?.alignment ?? ''),
                    (this.config.tabs?.size ?? ''),
                    (this.config.tabs?.style ?? ''),
                    (this.config.tabs?.width ?? ''),
                ).filter(Boolean)
            },
        },

        methods: {
            onEditTabName(evt, index) {
                this.entity.content.tabs[index].name = evt.target.innerText;
            },

            addTabs() {
                let template = cloneDeep(this.entity.content.template);

                this.entity.content.tabs.push(template);
            },

            deleteTabs(index) {
                let self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.entity.content.tabs.splice(index, 1);
                    }
                });
            },

            onSelectedIcon(icon) {
                this.entity.content.tabs[this.selectedIndex].icon = icon;
            },

            removeIcon() {
                this.entity.content.tabs[this.selectedIndex].icon = null;
            }
        }
    }
</script>