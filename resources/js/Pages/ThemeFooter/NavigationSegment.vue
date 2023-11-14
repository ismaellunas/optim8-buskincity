<template>
    <draggable
        handle=".handle-segment"
        item-key="id"
        class="has-background-light p-2"
        :animation="300"
        :group="{ name: 'segment' }"
        :list="computedMenuItems"
    >
        <template #item="{ element, index }">
            <div class="columns">
                <div class="column">
                    <navigation-menu
                        :is-child="false"
                        :menu-items="element.children"
                        :locale-options="localeOptions"
                        :selected-locale="selectedLocale"
                        :segment-index="index"
                        @duplicate-menu-item="openDuplicateModal"
                        @edit-row="editRow"
                        @open-form-modal="openFormModal"
                    >
                        <template #header>
                            <div
                                class="field is-grouped pl-4 pr-2 py-2 has-background-dark"
                                style="width: 100%"
                            >
                                <div class="control">
                                    <div class="buttons">
                                        <biz-button-icon
                                            type="button"
                                            class="handle-segment"
                                            :icon="icon.move"
                                            :title="i18n.drag_and_drop"
                                        />

                                        <biz-button-icon
                                            type="button"
                                            :icon="icon.up"
                                            :disabled="index == 0"
                                            @click="moveSegment('up', index)"
                                        />

                                        <biz-button-icon
                                            type="button"
                                            :icon="icon.down"
                                            :disabled="index == (computedMenuItems.length - 1)"
                                            @click="moveSegment('down', index)"
                                        />
                                    </div>
                                </div>

                                <div class="control is-expanded">
                                    <biz-input
                                        v-model="element.title"
                                        placeholder="Segment Name"
                                        required
                                    />
                                </div>

                                <div class="control">
                                    <biz-button-icon
                                        type="button"
                                        class="is-ghost"
                                        :icon="icon.remove"
                                        icon-class="has-text-white"
                                        @click="deleteRow(index)"
                                    />
                                </div>
                            </div>
                        </template>
                    </navigation-menu>
                </div>

                <navigation-form-menu
                    v-if="isModalOpen"
                    :base-route-name="baseRouteName"
                    :errors="menuItemErrors"
                    :menu="menu"
                    :menu-item="selectedMenuItem"
                    :selected-locale="selectedLocale"
                    @add-menu-item="addMenuItem"
                    @close="closeModal()"
                    @update-menu-item="updateMenuItem"
                />
            </div>
        </template>
    </draggable>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasTranslation from '@/Mixins/HasTranslation';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizInput from '@/Biz/Input.vue';
    import Draggable from "vuedraggable";
    import icon from '@/Libs/icon-class';
    import NavigationFormMenu from '@/Pages/ThemeHeader/NavigationFormMenuItem.vue';
    import NavigationMenu from './NavigationMenu.vue';
    import { usePage } from '@inertiajs/vue3';
    import { confirmDelete } from '@/Libs/alert';
    import { cloneDeep } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';
    import { moveItemUp, moveItemDown } from '@/Libs/menu-builder';

    export default {
        name: 'NavigationSegment',

        components: {
            BizInput,
            NavigationFormMenu,
            NavigationMenu,
            Draggable,
            BizIcon,
            BizButtonIcon,
        },

        mixins: [
            MixinHasModal,
            MixinHasTranslation,
        ],

        props: {
            menu: {
                type: Object,
                required: true,
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
            }
        },

        emits: [
            'duplicate-menu-item',
            'edit-row',
            'menu-items',
            'open-duplicate-modal',
            'open-form-modal',
        ],

        setup(props, {emit}) {
            return {
                baseRouteName: usePage().props.baseRouteName ?? null,
                validationRoute: route('admin.api.theme.header.menu-item.validate'),
                computedMenuItems: useModelWrapper(props, emit, 'menuItems'),
                icon,
            };
        },

        data() {
            return {
                menuItemErrors: {},
                selectedIndex: null,
                selectedMenuItem: {},
            };
        },

        methods: {
            editRow(menuItem) {
                this.selectedMenuItem = menuItem;
                this.menuItemErrors = {};
                this.openModal();
            },

            deleteRow(index) {
                const self = this;
                let message = "";

                if (self.computedMenuItems[index].children.length > 0) {
                    message = self.i18n.delete_segment_message;
                }

                confirmDelete(self.i18n.are_you_sure, message).then((result) => {
                    if (result.isConfirmed) {
                        self.$emit('menu-items', self.computedMenuItems.splice(index, 1));
                    }
                });
            },

            openFormModal(index) {
                this.selectedMenuItem = {};
                this.menuItemErrors = {};
                this.selectedIndex = index;

                this.openModal();
            },

            openDuplicateModal(menuItem, segmentIndex) {
                const cloneMenuItem = cloneDeep(menuItem);
                cloneMenuItem['id'] = null;
                cloneMenuItem['parent_id'] = null;
                cloneMenuItem['children'] = [];
                cloneMenuItem['locale'] = this.selectedLocale;

                this.$emit('open-duplicate-modal', cloneMenuItem, segmentIndex);
            },

            addMenuItem(menuItem) {
                const self = this;
                let menuItems = self.menuItems;

                axios.post(self.validationRoute, menuItem)
                    .then(() => {
                        menuItems[self.selectedIndex].children.push(
                            cloneDeep(menuItem)
                        );

                        self.selectedIndex = null;

                        self.closeModal();
                    })
                    .catch((error) => {
                        self.menuItemErrors = error.response.data.errors;
                    })
            },

            updateMenuItem(menuItem) {
                const self = this;

                axios.post(self.validationRoute, menuItem)
                    .then(() => {
                        self.updateSelectedMenu(menuItem);
                        self.closeModal();
                    })
                    .catch((error) => {
                        self.menuItemErrors = error.response.data.errors;
                    });
            },

            updateSelectedMenu(menuItem) {
                this.selectedMenuItem['title'] = menuItem['title'];
                this.selectedMenuItem['type'] = menuItem['type'];
                this.selectedMenuItem['url'] = menuItem['url'];
                this.selectedMenuItem['is_blank'] = menuItem['is_blank'];
                this.selectedMenuItem['menu_itemable_id'] = menuItem['menu_itemable_id'];
            },

            moveSegment(type, index) {
                switch (type) {
                case 'up':
                    moveItemUp(index, this.computedMenuItems);
                    break;

                case 'down':
                    moveItemDown(index, this.computedMenuItems);
                    break;
                }
            },
        },
    }
</script>