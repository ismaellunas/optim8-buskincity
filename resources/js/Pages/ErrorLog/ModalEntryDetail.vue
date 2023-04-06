<template>
    <biz-modal-card
        content-class="is-huge"
        :is-close-hidden="true"
        @close="$emit('close-modal')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ i18n.error_log_details }}
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="$emit('close-modal')"
            />
        </template>

        <biz-table class="is-striped is-fullwidth">
            <tbody>
                <tr>
                    <td width="30%">
                        {{ i18n.created_at }}
                    </td>
                    <td>{{ entry.createdAtFormatted }}</td>
                </tr>
                <tr>
                    <td>{{ i18n.url }}</td>
                    <td>{{ entry.url }}</td>
                </tr>
                <tr>
                    <td>{{ i18n.file }}</td>
                    <td>{{ entry.file ?? '-' }}</td>
                </tr>
                <tr>
                    <td>{{ i18n.line }}</td>
                    <td>{{ entry.line ?? '-' }}</td>
                </tr>
                <tr>
                    <td>{{ i18n.total_hit }}</td>
                    <td>{{ entry.total_hit }}</td>
                </tr>
                <tr>
                    <td>{{ i18n.message }}</td>
                    <td>{{ entry.message }}</td>
                </tr>
                <tr>
                    <td>{{ i18n.trace }}</td>
                    <td v-if="isTraceExists">
                        <biz-table
                            id="trace"
                            class="is-bordered is-fullwidth"
                            style="word-break: break-word;"
                        >
                            <thead>
                                <tr>
                                    <th>{{ i18n.file }}</th>
                                    <th>{{ i18n.line }}</th>
                                    <th>{{ i18n.function }}</th>
                                    <th>{{ i18n.class }}</th>
                                    <th>{{ i18n.type }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="(trace, index) in entry.trace"
                                    :key="index"
                                >
                                    <td>{{ trace.file ?? '-' }}</td>
                                    <td>{{ trace.line ?? '-' }}</td>
                                    <td>{{ trace.function ?? '-' }}</td>
                                    <td>{{ trace.class ?? '-' }}</td>
                                    <td>{{ trace.type ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </biz-table>
                    </td>
                    <td v-else>
                        -
                    </td>
                </tr>
            </tbody>
        </biz-table>

        <template #footer>
            <div
                class="columns mx-0"
                style="width: 100%"
            >
                <div class="column px-0">
                    <div class="is-pulled-right">
                        <biz-button @click="$emit('close-modal')">
                            {{ i18n.close }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizTable from '@/Biz/Table.vue';
    import { isEmpty, upperFirst } from 'lodash';

    export default {
        name: 'ModalEntryDetail',

        components: {
            BizButton,
            BizModalCard,
            BizTable,
        },

        inject: {
            i18n: { default: () => ({
                error_log_details : 'Error Log Details',
                created_at : 'Created At',
                url : 'URL',
                file : 'File',
                line : 'Line',
                total_hit : 'Total Hit',
                message : 'Message',
                trace : 'Trace',
                actions : 'Actions',
                function : 'Function',
                class : 'Class',
                type : 'Type',
                close : 'Close',
            }) }
        },

        props: {
            entry: { type: Object, default: () => {}},
        },

        emits: [
            'open-modal',
            'close-modal',
        ],

        computed: {
            isTraceExists() {
                return !isEmpty(this.entry.trace);
            },
        },

        methods: {
            upperFirst,
        },
    };
</script>

<style scoped>
    #trace > thead > tr > th:nth-child(1) {
        width: 30%;
    }

    #trace > thead > tr > th:nth-child(2),th:nth-child(5) {
        width: 10%;
    }
</style>
