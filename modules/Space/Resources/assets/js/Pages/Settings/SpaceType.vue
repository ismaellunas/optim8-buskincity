<template>
    <div>
        <biz-list-section>
            <template #title>
                {{ i18n.spaceType }}
            </template>

            <div class="columns">
                <div class="column is-4">
                    <biz-filter-search
                        v-model="term"
                        @search="search"
                    />
                </div>

                <div class="column has-text-right">
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

            <biz-table-index
                :is-ajax="true"
                :records="types"
                :query-params="queryParams"
                @on-clicked-pagination="getSpaceTypes"
            >
                <template #thead>
                    <tr>
                        <th>Name</th>
                        <th>
                            <div class="level-right">
                                Actions
                            </div>
                        </th>
                    </tr>
                </template>

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
            </biz-table-index>
        </biz-list-section>

        <space-type-form-modal
            v-if="isModalOpen"
            :selected-type="selectedType"
            @close="closeModal()"
            @on-submit="onSubmit"
        >
            <template #header>
                {{ !selectedType.id ? i18n.createSpaceType : i18n.editSpaceType }}
            </template>
        </space-type-form-modal>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButton from '@/Biz/Button';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizListSection from '@/Biz/ListSection';
    import BizTableIndex from '@/Biz/TableIndex';
    import icon from '@/Libs/icon-class';
    import SpaceTypeFormModal from './SpaceTypeFormModal';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        name: 'SpaceType',

        components: {
            BizButton,
            BizFilterSearch,
            BizListSection,
            BizTableIndex,
            SpaceTypeFormModal,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasModal
        ],

        inject: [
            'i18n',
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
