<template>
    <app-layout>
        <template #header>
            Main Menu
        </template>

        <sdb-error-notifications :errors="$page.props.errors"/>

        <form method="post">
            <div class="box mb-6">
                <div class="columns">
                    <div class="column">
                        <fieldset>
                            <sdb-form-input
                                v-model="form.title"
                                label="Title"
                                required
                                :message="form.errors.title"
                            ></sdb-form-input>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="box mb-3">
                <div class="columns">
                    <div class="column">
                        <div class="is-pulled-left">
                            <b>Menu Items</b>
                        </div>
                    </div>
                    <div class="column">
                        <div class="is-pulled-right">
                            <sdb-button
                                type="button"
                                class="is-primary"
                                @click.prevent="newRow()"
                            >
                                <span class="icon is-small">
                                    <i class="fas fa-plus"></i>
                                </span>
                                <span>Add Menu Item</span>
                            </sdb-button>
                        </div>
                    </div>
                </div>
                <div class="columns">
                    <div class="column">
                        <menu-nested
                            :items="menuItems"
                            @edit-row="editRow"
                            @delete-row="deleteRow"
                        ></menu-nested>
                    </div>
                </div>
            </div>
            <div class="columns">
                <div class="column">
                    <div class="is-pulled-right">
                        <sdb-button type="button">
                            <span>Cancel</span>
                        </sdb-button>
                        <sdb-button
                            type="button"
                            class="is-primary ml-2"
                        >
                            <span>Save</span>
                        </sdb-button>
                    </div>
                </div>
            </div>
        </form>
        <modal-form-menu-item
            v-if="isModalOpen"
            :base-route-name="baseRouteName"
            :menu="menu"
            :menuItem="selectedMenuItems"
            @close="closeModal()"
        ></modal-form-menu-item>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasModal from '@/Mixins/HasModal';
    import ModalFormMenuItem from './FormMenuItem';
    import MenuNested from './MenuNested';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';;
    import SdbFormInput from '@/Sdb/Form/Input';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { confirmDelete } from '@/Libs/alert';
    import { merge, filter } from 'lodash';
    import { isEmpty } from '@/Libs/utils';

    export default {
        components: {
            AppLayout,
            ModalFormMenuItem,
            MenuNested,
            SdbButton,
            SdbButtonLink,
            SdbErrorNotifications,
            SdbFormInput,
        },
        mixins: [
            MixinHasModal,
        ],
        props: {
            baseRouteName: String,
            menu: Object,
            menuItems: Object,
        },
        setup(props) {
            const form = merge(
                props.menu,
                {
                    menu_items: {
                        en: [],
                    }
                }
            );
            return {
                form: useForm(form),
            }
        },
        data() {
            return {
                selectedMenuItems: {},
            };
        },
        methods: {
            newRow() {
                this.selectedMenuItems = {};
                this.isModalOpen = true;
            },

            deleteRow(menuItemId) {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy.menu_item', menuItemId)
                        );
                    }
                });
            },

            editRow(menuItemId) {
                let menuItem = filter(this.menuItems, { id: menuItemId });
                if (!isEmpty(menuItem)) {
                    this.selectedMenuItems = menuItem[0];
                } else {
                    this.selectedMenuItems = {};
                }

                this.openModal();
            }
        }
    }
</script>
