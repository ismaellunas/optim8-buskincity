<template>
    <biz-modal-card
        content-class="is-huge"
        :is-close-hidden="true"
        @close="$emit('close-modal')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                Request
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="$emit('close-modal')"
            />
        </template>

        <div class="box">
            <biz-table class="is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th
                            colspan="100"
                        >
                            Request Details
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Time</th>
                        <td>{{ entry.created_at }}</td>
                    </tr>
                    <tr>
                        <th>Hostname</th>
                        <td>{{ entry.content.hostname }}</td>
                    </tr>
                    <tr>
                        <th>Method</th>
                        <td>
                            <biz-tag>
                                {{ entry.content.method }}
                            </biz-tag>
                        </td>
                    </tr>
                    <tr>
                        <th>Controller Action</th>
                        <td>{{ entry.content.controller_action }}</td>
                    </tr>
                    <tr>
                        <th>Middleware</th>
                        <td>{{ entry.content.middleware.join(',') }}</td>
                    </tr>
                    <tr>
                        <th>Path</th>
                        <td>{{ entry.content.uri }}</td>
                    </tr>
                    <tr>
                        <th>Duration</th>
                        <td>{{ entry.content.duration }} ms</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <biz-tag :class="statusCodeColor(entry.content.response_status)">
                                {{ entry.content.response_status }}
                            </biz-tag>
                        </td>
                    </tr>
                    <tr>
                        <th>IP address</th>
                        <td>{{ entry.content.ip_address }}</td>
                    </tr>
                    <tr>
                        <th>Memory</th>
                        <td>{{ entry.content.memory }} MB</td>
                    </tr>
                </tbody>
            </biz-table>
        </div>

        <div
            v-if="entry.content?.user"
            class="box"
        >
            <biz-table
                class="is-striped is-hoverable is-fullwidth"
            >
                <thead>
                    <tr>
                        <th
                            colspan="100"
                        >
                            Authenticated User
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{{ entry.content.user.id }}</td>
                    </tr>
                    <tr>
                        <th>Email address</th>
                        <td>{{ entry.content.user.email }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ entry.content.user.name }}</td>
                    </tr>
                </tbody>
            </biz-table>
        </div>

        <template #footer>
            <div
                class="columns mx-0"
                style="width: 100%"
            >
                <div class="column px-0">
                    <div class="is-pulled-right">
                        <biz-button @click="$emit('close-modal')">
                            Cancel
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
    import BizTag from '@/Biz/Tag';
    import { statusCodeColor } from '@/Libs/utils';

    export default {
        name: 'ModalEntryDetail',

        components: {
            BizButton,
            BizModalCard,
            BizTable,
            BizTag,
        },

        props: {
            entry: { type: Object, default: () => {}},
        },

        emits: [
            'open-modal',
            'close-modal',
        ],

        methods: {
            statusCodeColor: statusCodeColor,
        },
    };
</script>
