<template>
    <div class="column is-6">
        <biz-panel class="is-white">
            <template #heading>
                <div class="columns">
                    <div class="column">
                        {{ title }}
                    </div>
                    <div class="column">
                        <biz-button-link
                            v-if="data.permissions.add"
                            class="is-primary is-small is-pulled-right"
                            :href="route(data.baseRouteName+'.create')"
                        >
                            <span class="icon is-small">
                                <i class="fas fa-plus" />
                            </span>
                            <span>Add New</span>
                        </biz-button-link>
                    </div>
                </div>
            </template>

            <template #default>
                <template v-if="data.records.length > 0">
                    <biz-panel-block
                        v-for="record in data.records"
                        :key="record.id"
                    >
                        <div
                            class="media"
                            style="width: 100%"
                        >
                            <biz-image
                                class="media-left"
                                ratio="is-64x64"
                                rounded="is-rounded"
                                :src="record.profile_photo_url ?? `/images/profile-picture-default.png`"
                            />

                            <div class="media-content">
                                <div class="content">
                                    <p>
                                        <strong>{{ record.full_name }}</strong>
                                        <br>

                                        <span>
                                            {{ record.email }}
                                        </span>
                                        <br>

                                        <span class="is-size-7 has-text-grey">
                                            Registered, {{ record.registered_at }}
                                        </span>
                                        <br>
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="showAction(record)"
                                class="media-right"
                            >
                                <biz-dropdown class-button="is-ghost">
                                    <template #trigger>
                                        <span class="icon">
                                            <i class="fas fa-ellipsis-h" />
                                        </span>
                                    </template>

                                    <biz-dropdown-item>
                                        <biz-link
                                            v-if="data.permissions.edit"
                                            :href="route(data.baseRouteName+'.edit', {id: record.id})"
                                        >
                                            <span class="icon is-small mr-2">
                                                <i class="fas fa-pen" />
                                            </span>
                                            <span>Edit</span>
                                        </biz-link>
                                    </biz-dropdown-item>

                                    <template
                                        v-if="data.permissions.delete
                                            && record.id !== $page.props.user.id"
                                    >
                                        <biz-dropdown-item>
                                            <a
                                                @click.prevent="deleteUserModal(record)"
                                            >
                                                <span class="icon is-small mr-2">
                                                    <i class="far fa-trash-alt" />
                                                </span>
                                                <span>Delete</span>
                                            </a>
                                        </biz-dropdown-item>
                                        <biz-dropdown-item>
                                            <a
                                                v-if="!record.is_suspended"
                                                @click.prevent="suspendUser(record)"
                                            >
                                                <span class="icon is-small mr-2">
                                                    <i class="fas fa-ban" />
                                                </span>
                                                <span>Suspend</span>
                                            </a>
                                            <a
                                                v-if="record.is_suspended"
                                                @click.prevent="unsuspendUser(record)"
                                            >
                                                <span class="icon is-small mr-2">
                                                    <i class="fas fa-hands-helping" />
                                                </span>
                                                <span>Unsuspend</span>
                                            </a>
                                        </biz-dropdown-item>
                                    </template>
                                </biz-dropdown>
                            </div>
                        </div>
                    </biz-panel-block>
                </template>

                <template v-else>
                    <biz-panel-block>
                        Data empty.
                    </biz-panel-block>
                </template>

                <biz-panel-block>
                    <div
                        class="level"
                        style="width: 100%"
                    >
                        <div class="level-left" />
                        <div class="level-right">
                            <biz-button-link
                                class="is-primary is-outlined is-small"
                                :href="route(data.baseRouteName+'.index')"
                            >
                                View All
                            </biz-button-link>
                        </div>
                    </div>
                </biz-panel-block>

                <modal-form-delete
                    v-if="isModalOpen"
                    :get-candidates-route="data.baseRouteName+'.reassignment-candidates'"
                    :user="selectedUser"
                    @close="closeModal"
                    @delete-user="deleteUser"
                />
            </template>
        </biz-panel>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizImage from '@/Biz/Image';
    import BizLink from '@/Biz/Link';
    import BizPanel from '@/Biz/Panel';
    import BizPanelBlock from '@/Biz/PanelBlock';
    import ModalFormDelete from '@/Pages/User/ModalFormDelete';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';

    export default {
        name: 'User',

        components: {
            BizButtonLink,
            BizDropdown,
            BizDropdownItem,
            BizImage,
            BizLink,
            BizPanel,
            BizPanelBlock,
            ModalFormDelete,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
        ],

        props: {
            data: {
                type: Object,
                required: true,
            },

            title: {
                type: String,
                default: "",
            },
        },

        data() {
            return {
                selectedUser: null,
            };
        },

        methods: {
            onError() {
                oopsAlert();
            },

            onSuccess(page) {
                successAlert(page.props.flash.message);
                this.closeModal();
            },

            showAction(record) {
                const permissions = this.data.permissions;
                const canDeleteUser = record.id !== this.$page.props.user.id;

                if (permissions.edit || permissions.delete) {
                    if (!permissions.edit && permissions.delete && !canDeleteUser) {
                        return false;
                    }

                    return true;
                } else {
                    return false;
                }
            },

            deleteUserModal(user) {
                this.selectedUser = user;
                this.openModal();
            },

            deleteUser(form) {
                const self = this;

                confirmDelete(
                    'Are you sure?',
                    'Once you hit "Confirm Deletion", the user will be permanently removed.',
                    'Confirm Deletion'
                ).then(result => {
                    if (result.isConfirmed) {
                        const userId = self.selectedUser.id;

                        form.delete(
                            route(self.data.baseRouteName+'.destroy', userId),
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: self.onError,
                                onSuccess: self.onSuccess,
                            }
                        );
                    }
                })
            },

            suspendUser(user) {
                const self = this;

                confirmDelete(
                    'Are you sure?',
                    'The user will be suspended.'
                ).then(result => {
                    if (result.isConfirmed) {

                        self.$inertia.post(
                            route(self.data.baseRouteName+'.suspend', user.id),
                            {},
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: self.onError,
                                onSuccess: self.onSuccess,
                            }
                        );
                    }
                });
            },

            unsuspendUser(user) {
                const self = this;

                confirmDelete(
                    'Are you sure?',
                    'The user will be unsuspended.'
                ).then(result => {
                    if (result.isConfirmed) {

                        self.$inertia.post(
                            route(self.data.baseRouteName+'.unsuspend', user.id),
                            {},
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: self.onError,
                                onSuccess: self.onSuccess,
                            }
                        );
                    }
                });
            },
        }
    }
</script>