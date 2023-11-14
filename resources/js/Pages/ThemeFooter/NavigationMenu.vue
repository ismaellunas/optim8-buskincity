<template>
    <div class="card">
        <div class="card-header">
            <slot name="header" />
        </div>

        <div class="card-content has-background-light p-2">
            <draggable
                handle=".handle-menu"
                item-key="id"
                class="draggable-area"
                :animation="300"
                :group="{ name: 'g1' }"
                :list="menuItems"
            >
                <template #item="{ element, index }">
                    <theme-menu-item
                        class="mb-1"
                        :is-child="isChild"
                        :is-down-button-disabled="index == (menuItems.length - 1)"
                        :is-up-button-disabled="index == 0"
                        :locale-options="localeOptions"
                        :menu-item-index="index"
                        :menu-item="element"
                        :selected-locale="selectedLocale"
                        @delete-row="deleteRow"
                        @duplicate-menu-item="duplicateMenuItem"
                        @edit-row="$emit('edit-row', $event)"
                        @move-menu-item="moveMenuItem"
                    />
                </template>
            </draggable>
        </div>

        <div class="card-footer p-2">
            <div
                class="has-text-right"
                style="width: 100%"
            >
                <biz-button-icon
                    type="button"
                    class="is-primary"
                    :icon="icon.add"
                    @click="$emit('open-form-modal', segmentIndex)"
                >
                    <span>
                        {{ sentenceCase(i18n.add_menu_item) }}
                    </span>
                </biz-button-icon>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasTranslation from '@/Mixins/HasTranslation';
    import Draggable from "vuedraggable";
    import ThemeMenuItem from '@/Biz/ThemeMenuItem.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import icon from '@/Libs/icon-class';
    import { usePage } from '@inertiajs/vue3';
    import { confirmDelete } from '@/Libs/alert';
    import { sentenceCase } from 'change-case';
    import { useModelWrapper } from '@/Libs/utils';
    import { moveItemUp, moveItemDown } from '@/Libs/menu-builder';

    export default {
        name: 'NavigationMenu',

        components: {
            Draggable,
            ThemeMenuItem,
            BizButtonIcon,
        },

        mixins: [
            MixinHasTranslation,
        ],

        props: {
            isChild: {
                type: Boolean,
                default: true,
            },
            menuItems: {
                type: Array,
                default:() => {},
            },
            localeOptions: {
                type: Array,
                default:() => {},
            },
            selectedLocale: {
                type: String,
                default: "en"
            },
            segmentIndex: {
                type: Number,
                required: true,
            }
        },

        emits: [
            'duplicate-menu-item',
            'edit-row',
            'menu-items',
            'open-form-modal',
            'update-last-data-menu-items'
        ],

        setup(props, {emit}) {
            return {
                baseRouteName: usePage().props.baseRouteName ?? null,
                computedMenuItems: useModelWrapper(props, emit, 'menuItems'),
                icon
            };
        },

        methods: {
            editRow(menuItem) {
                this.$emit('edit-row', menuItem);
            },

            deleteRow(index) {
                const self = this;
                const menuItems = this.menuItems;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$emit('menu-items', menuItems.splice(index, 1));
                        self.updateLastDataMenuItems();
                    }
                });
            },

            duplicateMenuItem(menuItem) {
                this.$emit('duplicate-menu-item', menuItem, this.segmentIndex);
            },

            updateLastDataMenuItems() {
                this.$emit('update-last-data-menu-items');
            },

            moveMenuItem(type, index) {
                switch (type) {
                case 'up':
                    moveItemUp(index, this.computedMenuItems);
                    break;

                case 'down':
                    moveItemDown(index, this.computedMenuItems);
                    break;
                }
            },

            sentenceCase,
        },
    }
</script>