<template>
    <div class="columns">
        <div class="column">
            <b>Space Types</b>
        </div>

        <div class="column">
            <div class="columns">
                <div class="column">
                    <div class="is-pulled-left">
                        <biz-filter-search
                            v-model="term"
                            @search="search"
                        />
                    </div>
                </div>
                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button
                            type="button"
                            class="is-primary"
                            @click="openModalCreate()"
                        >
                            <span class="icon is-small">
                                <i :class="icon.add" />
                            </span>
                            <span>Add New</span>
                        </biz-button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <biz-table class="is-fullwidth">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>
                                <div class="level-right">
                                    Actions
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="record in types.data"
                            :key="record.id"
                        >
                            <td>{{ record.name }}</td>
                            <td>
                                <div class="level-right">
                                    <biz-button
                                        type="button"
                                        class="is-ghost has-text-black"
                                        @click="openModalEdit(record)"
                                    >
                                        <span class="icon is-small">
                                            <i :class="icon.edit" />
                                        </span>
                                    </biz-button>
                                    <biz-button
                                        type="button"
                                        class="is-ghost has-text-black ml-1"
                                        @click="onDelete(record)"
                                    >
                                        <span class="icon is-small">
                                            <i :class="icon.remove" />
                                        </span>
                                    </biz-button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </biz-table>
            </div>

            <biz-pagination
                :is-ajax="true"
                :links="types.links"
                :query-params="queryParams"
                @on-clicked-pagination="getSpaceTypes"
            />
        </div>

        <space-type-form-modal
            v-if="isModalOpen"
            :selected-type="selectedType"
            @close="closeModal()"
            @on-submit="onSubmit"
        />
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButton from '@/Biz/Button';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import icon from '@/Libs/icon-class';
    import SpaceTypeFormModal from './SpaceTypeFormModal';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        name: 'SpaceType',

        components: {
            BizButton,
            BizFilterSearch,
            BizPagination,
            BizTable,
            SpaceTypeFormModal,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasModal
        ],

        data() {
            return {
                baseRouteName: 'admin.spaces.settings.space-types',
                icon,
                queryParams: {},
                selectedType: {},
                term: null,
                types: {},
            };
        },

        mounted() {
            this.getSpaceTypes();
        },

        methods: {
            getSpaceTypes(url = null) {
                const self = this;
                url = url ?? route(self.baseRouteName + '.records');

                axios.get(url, {
                    params: self.queryParams,
                }).then((response) => {
                    self.types = response.data;
                });
            },

            refreshWithQueryParams() {
                this.getSpaceTypes();
            },

            openModalCreate() {
                this.selectedType = {};

                this.openModal();
            },

            openModalEdit(type) {
                this.selectedType = type;

                this.openModal();
            },

            onSubmit(form) {
                form = useForm(form);

                if (!form.id) {
                    this.onStore(form);
                } else {
                    this.onUpdate(form);
                }
            },

            onStore(form) {
                const self = this;

                form.post(route(self.baseRouteName + '.store'), {
                    onStart: () => {
                        self.onStartLoadingOverlay();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.getSpaceTypes();
                    },
                    onError: () => { oopsAlert() },
                    onFinish: () => {
                        self.selectedType = {};

                        self.closeModal();
                        self.onEndLoadingOverlay();
                    }
                });
            },

            onUpdate(form) {
                const self = this;

                form.put(route(self.baseRouteName+'.update', form.id), {
                    onStart: () => {
                        self.onStartLoadingOverlay();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.getSpaceTypes();
                    },
                    onError: () => { oopsAlert() },
                    onFinish: () => {
                        self.selectedType = {};

                        self.closeModal();
                        self.onEndLoadingOverlay();
                    }
                });
            },

            onDelete(type) {
                const self = this;

                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(this.baseRouteName+'.destroy', type.id),
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: () => {
                                    oopsAlert();
                                },
                                onSuccess: (page) => {
                                    successAlert(page.props.flash.message);
                                    self.getSpaceTypes();
                                },
                            }
                        );
                    }
                })
            },
        }
    };
</script>
