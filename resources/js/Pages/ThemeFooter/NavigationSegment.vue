<template>
    <draggable
        handle=".handle-segment"
        item-key="id"
        :animation="300"
        :class="panelClasses"
        :group="{ name: 'segment' }"
        :list="menuItems"
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
                            <div class="panel-heading">
                                <div class="field is-grouped">
                                    <div class="control is-align-items-center is-flex">
                                        <span class="panel-icon handle-segment">
                                            <i
                                                class="fas fa-bars"
                                                aria-hidden="true"
                                            />
                                        </span>
                                    </div>
                                    <div class="control is-expanded">
                                        <biz-input
                                            v-model="element.title"
                                            placeholder="Segment Name"
                                            required
                                        />
                                    </div>
                                    <div class="control">
                                        <biz-button
                                            type="button"
                                            class="is-ghost"
                                            @click="deleteRow(index)"
                                        >
                                            <span class="icon has-text-danger">
                                                <i
                                                    class="fas fa-trash"
                                                    aria-hidden="true"
                                                />
                                            </span>
                                        </biz-button>
                                    </div>
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
    import BizButton from '@/Biz/Button';
    import BizInput from '@/Biz/Input';
    import Draggable from "vuedraggable";
    import NavigationFormMenu from '@/Pages/ThemeHeader/NavigationFormMenuItem';
    import NavigationMenu from './NavigationMenu';
    import { usePage } from '@inertiajs/inertia-vue3';
    import { confirmDelete } from '@/Libs/alert';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'NavigationSegment',

        components: {
            BizButton,
            BizInput,
            NavigationFormMenu,
            NavigationMenu,
            Draggable,
        },

        mixins: [
            MixinHasModal,
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

        setup() {
            return {
                baseRouteName: usePage().props.value.baseRouteName ?? null,
                validationRoute: route('admin.api.theme.header.menu-item.validate'),
            };
        },

        data() {
            return {
                menuItemErrors: {},
                selectedIndex: null,
                selectedMenuItem: {},
            };
        },

        computed: {
            panelClasses() {
                return {
                    'panel': true,
                    'pl-4': true,
                };
            },
        },

        methods: {
            editRow(menuItem) {
                this.selectedMenuItem = menuItem;
                this.menuItemErrors = {};
                this.openModal();
            },

            deleteRow(index) {
                const self = this;
                const menuItems = this.menuItems;
                let message = "";

                if (menuItems[index].children.length > 0) {
                    message = "A menu item will deleted too."
                }

                confirmDelete("Are you sure?", message).then((result) => {
                    if (result.isConfirmed) {
                        self.$emit('menu-items', menuItems.splice(index, 1));
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

                menuItems[self.selectedIndex].children.push(
                    cloneDeep(menuItem)
                );

                axios.post(self.validationRoute, menuItem)
                    .then(() => {
                        self.$emit('menu-items', menuItems);
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
        },
    }
</script>

<style scoped>
    .handle-segment {
        cursor: pointer;
    }
</style>