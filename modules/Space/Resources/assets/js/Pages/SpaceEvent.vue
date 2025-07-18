<template>
    <div>
        <div class="columns">
            <div class="column has-text-right">
                <biz-button
                    type="button"
                    class="is-primary"
                    @click="openModalCreate()"
                >
                    <span class="icon is-small">
                        <i :class="icon.add" />
                    </span>
                    <span>{{ i18n.add_new }}</span>
                </biz-button>
            </div>
        </div>

        <biz-table-index
            :records="events"
            :is-ajax-pagination="true"
            :query-params="queryParams"
            @on-clicked-pagination="getRecords"
        >
            <template #thead>
                <tr>
                    <th>{{ i18n.title }}</th>
                    <th>{{ i18n.started_at }}</th>
                    <th>{{ i18n.ended_at }}</th>
                    <th>{{ i18n.status }}</th>
                    <th>
                        <div class="level-right">
                            {{ i18n.actions }}
                        </div>
                    </th>
                </tr>
            </template>

            <tr
                v-for="record in events.data"
                :key="record.id"
            >
                <td>{{ record.title }}</td>
                <td>{{ record.started_at }}</td>
                <td>{{ record.ended_at }}</td>
                <td>
                    <biz-tag
                        class="is-small is-rounded"
                        :class="{ 'is-success': record.status == 'published' }"
                    >
                        {{ record.display_status }}
                    </biz-tag>
                </td>
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

        <space-event-form-modal
            v-if="isModalOpen"
            :selected-event="selectedEvent"
            :space="space"
            @close="closeModal()"
            @after-submit="afterSubmit"
        />
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import BizTag from '@/Biz/Tag.vue';
    import SpaceEventFormModal from './SpaceEventFormModal.vue';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';

    export default {
        name: 'SpaceEvent',

        components: {
            BizButton,
            BizTableIndex,
            BizTag,
            SpaceEventFormModal,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
            MixinHasPageErrors,
        ],

        inject: {
            i18n: { default: () => ({
                add_new: 'Add new',
                title: 'Title',
                started_at: 'Started at',
                ended_at: 'Ended at',
                actions: 'Actions',
                add_new_event: 'Add new event',
                started_and_ended_at: 'Started at and ended at',
                description: 'Description',
                are_you_sure: 'Are you sure?',
            }) }
        },

        props: {
            space: { type: Object, required: true },
        },

        data() {
            return {
                baseRouteName: 'admin.spaces.events',
                icon,
                queryParams: {},
                selectedEvent: {},
                term: null,
                events: {},
            };
        },

        beforeMount() {
            this.getRecords();
        },

        methods: {
            getRecords(url = null) {
                const self = this;

                url = url ?? route(self.baseRouteName + '.records', this.space.id);

                axios.get(url, {
                    params: self.queryParams,
                }).then((response) => {
                    self.events = response.data;
                });
            },

            openModalEdit(event) {
                this.selectedEvent = event;

                this.openModal();
            },

            onDelete(record) {
                const self = this;

                confirmDelete(
                    self.are_you_sure,
                ).then(result => {
                    if (result.isConfirmed) {
                        const url = route('admin.spaces.events.destroy', [self.space.id, record.id]);

                        self.onStartLoadingOverlay();

                        axios
                            .delete(url)
                            .then(response => {
                                self.getRecords();
                                successAlert(response.data);
                            })
                            .catch(error => {
                                oopsAlert();
                            })
                            .then(() => {
                                self.onEndLoadingOverlay();
                            });
                    }
                })
            },

            openModalCreate() {
                this.selectedEvent = null;
                this.openModal();
            },

            afterSubmit() {
                this.getRecords();
            },
        },
    };
</script>
