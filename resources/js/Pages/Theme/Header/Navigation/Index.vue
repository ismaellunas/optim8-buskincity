<template>
    <app-layout>
        <template #header>
            Header
        </template>

        <sdb-error-notifications :errors="$page.props.errors"/>

        <div class="box mb-6">
            <sdb-tab>
                <ul>
                    <sdb-tab-list
                        v-for="(tab, index) in tabs"
                        :key="index"
                        :is-active="isTabActive(index)"
                    >
                        <a @click.prevent="setActiveTab(index)">
                            {{ tab.title }}
                        </a>
                    </sdb-tab-list>
                </ul>
            </sdb-tab>

            <div class="columns">
                <div class="column">
                    <div class="is-pulled-left">
                        <p>
                            <b>Menu Navigations</b><br>
                            Last saved: {{ lastSaved }}
                        </p>
                    </div>
                </div>
                <div class="column">
                    <div class="is-pulled-right">
                        <sdb-button
                            class="is-primary"
                            @click="isModalOpen = true"
                        >
                            <span class="icon is-small">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span>Add Menu</span>
                        </sdb-button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <sdb-table class="is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Menu Items</th>
                            <th>Status</th>
                            <th>
                                <div class="level-right">Actions</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in records.data" :key="record.id">
                            <th>{{ record.title }}</th>
                            <td>-</td>
                            <td>
                                <span
                                    v-if="record.is_active"
                                    class="tag is-success"
                                >Active</span>
                                <span
                                    v-else
                                    class="tag is-light"
                                >Inactive</span>
                            </td>
                            <td>
                                <div class="level-right">
                                    <sdb-button-link
                                        class="is-ghost has-text-black"
                                        :href="route(baseRouteName + '.edit', record.id)"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-pen"></i>
                                        </span>
                                    </sdb-button-link>
                                    <sdb-button
                                        class="is-ghost has-text-black ml-1"
                                        @click.prevent="deleteRow(record)"
                                    >
                                        <span class="icon is-small">
                                            <i class="far fa-trash-alt"></i>
                                        </span>
                                    </sdb-button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </sdb-table>
            </div>
            <sdb-pagination :links="records.links"></sdb-pagination>
        </div>
        <modal-form
            v-if="isModalOpen"
            :base-route-name="baseRouteName"
            @close="closeModal()"
        ></modal-form>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasTab from '@/Mixins/HasTab';
    import ModalForm from './Form';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbPagination from '@/Sdb/Pagination';
    import SdbTab from '@/Sdb/Tab';
    import SdbTable from '@/Sdb/Table';
    import SdbTabList from '@/Sdb/TabList';
    import { confirmDelete } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            ModalForm,
            SdbButton,
            SdbButtonLink,
            SdbErrorNotifications,
            SdbPagination,
            SdbTab,
            SdbTable,
            SdbTabList,
        },
        mixins: [
            MixinHasModal,
            MixinHasTab,
        ],
        props: {
            baseRouteName: String,
            lastSaved: String,
            records: Object,
        },
        setup() {
            return {
                tabs: {
                    layout: { title: 'Layout'},
                    navigation: {title: 'Navigation'},
                },
            }
        },
        data() {
            return {
                activeTab: 'navigation',
            };
        },
        methods: {
            onTabSelected(tab) {
                let routeName = this.baseRouteName+'.index';
                if (tab === 'layout') {
                    routeName = 'admin.theme.header.index';
                }
                this.$inertia.get(route(routeName));
            },
            deleteRow(record) {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', record.id)
                        );
                    }
                });
            },
        }
    }
</script>
