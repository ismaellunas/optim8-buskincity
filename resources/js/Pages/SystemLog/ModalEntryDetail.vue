<template>
    <biz-modal-card
        content-class="is-huge"
        :is-close-hidden="true"
        @close="$emit('close-modal')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ i18n.request }}
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
                            {{ i18n.request_details }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>{{ i18n.time }}</th>
                        <td>{{ entry.created_at }}</td>
                    </tr>
                    <tr>
                        <th>{{ i18n.hostname }}</th>
                        <td>{{ entry.content.hostname }}</td>
                    </tr>
                    <tr>
                        <th>{{ i18n.method }}</th>
                        <td>
                            <biz-tag>
                                {{ entry.content.method }}
                            </biz-tag>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ i18n.controller_action }}</th>
                        <td>{{ entry.content.controller_action }}</td>
                    </tr>
                    <tr>
                        <th>{{ i18n.middleware }}</th>
                        <td>{{ entry.content.middleware.join(',') }}</td>
                    </tr>
                    <tr>
                        <th>{{ i18n.path }}</th>
                        <td>{{ entry.content.uri }}</td>
                    </tr>
                    <tr>
                        <th>{{ i18n.duration }}</th>
                        <td>{{ entry.content.duration }} ms</td>
                    </tr>
                    <tr>
                        <th>{{ i18n.status }}</th>
                        <td>
                            <biz-tag :class="statusCodeColor(entry.content.response_status)">
                                {{ entry.content.response_status }}
                            </biz-tag>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ i18n.ip_address }}</th>
                        <td>{{ entry.content.ip_address }}</td>
                    </tr>
                    <tr>
                        <th>{{ i18n.memory }}</th>
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
                            {{ i18n.authenticated_user }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>{{ i18n.id }}</th>
                        <td>{{ entry.content.user.id }}</td>
                    </tr>
                    <tr>
                        <th>{{ i18n.email_address }}</th>
                        <td>{{ entry.content.user.email }}</td>
                    </tr>
                    <tr>
                        <th>{{ i18n.name }}</th>
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
                            {{ i18n.cancel }}
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
    import BizTag from '@/Biz/Tag.vue';
    import { statusCodeColor } from '@/Libs/utils';

    export default {
        name: 'ModalEntryDetail',

        components: {
            BizButton,
            BizModalCard,
            BizTable,
            BizTag,
        },

        inject: {
            i18n: { default: () => ({
                request : 'Request',
                request_details : 'Request Details',
                time : 'Time',
                hostname : 'Hostname',
                method : 'Method',
                controller_action : 'Controller Action',
                middleware : 'Middleware',
                path : 'Path',
                duration : 'Duration',
                status : 'Status',
                ip_address : 'Ip Address',
                memory : 'Memory',
                authenticated_user : 'Authenticated User',
                id : 'ID',
                email_address : 'Email Address',
                name : 'Name',
                cancel : 'Cancel',
            }) },
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
