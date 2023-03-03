<template>
    <biz-modal-card
        content-class="is-huge"
        :is-close-hidden="true"
        @close="$emit('close-modal')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                Error Log Details
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
                    <td width="30%">Created At</td>
                    <td>{{ entry.createdAtFormatted }}</td>
                </tr>
                <tr>
                    <td>URL</td>
                    <td>{{ entry.url }}</td>
                </tr>
                <tr>
                    <td>File</td>
                    <td>{{ entry.file ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Line</td>
                    <td>{{ entry.line ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Total Hit</td>
                    <td>{{ entry.total_hit }}</td>
                </tr>
                <tr>
                    <td>Message</td>
                    <td>{{ entry.message }}</td>
                </tr>
                <tr>
                    <td>Trace</td>
                    <td v-if="isTraceExists">
                        <biz-table
                            id="trace"
                            class="is-bordered is-fullwidth"
                            style="word-break: break-word;"
                        >
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Line</th>
                                    <th>Function</th>
                                    <th>Class</th>
                                    <th>Type</th>
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
                            Close
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
