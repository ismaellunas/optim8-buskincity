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

        <biz-table class="is-striped is-hoverable is-fullwidth">
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
                    <td>{{ entry.trace ?? '-' }}</td>
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
    import BizButton from '@/Biz/Button';
    import BizModalCard from '@/Biz/ModalCard';
    import BizTable from '@/Biz/Table';

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
    };
</script>
