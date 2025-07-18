<template>
    <biz-modal-card
        @close="close()"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ capitalCase(title) }}
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="close()"
            />
        </template>

        <h5 class="title is-5">
            {{ productName }}
        </h5>

        <biz-table is-fullwidth>
            <tr>
                <th><biz-icon :icon="icon.duration" /></th>
                <td>{{ event.duration }}</td>
            </tr>
            <tr>
                <th><biz-icon :icon="icon.timezone" /></th>
                <td>{{ event.display_timezone }}</td>
            </tr>
            <tr>
                <th><biz-icon :icon="icon.calendar" /></th>
                <td>{{ event.start_end_time }}, {{ event.date }}</td>
            </tr>
        </biz-table>

        <h4 class="title is-4">
            {{ i18n.are_you_sure_cancel_event }}
        </h4>

        <biz-form-textarea
            v-model="message"
            :label="i18n.message"
            placeholder="Please enter your reason or message here"
            rows="4"
            maxlength="500"
        />

        <template #footer>
            <div
                class="columns mx-0"
                style="width: 100%"
            >
                <div class="column px-0">
                    <div class="is-pulled-right">
                        <biz-button @click="close()">
                            {{ i18n.no }}
                        </biz-button>

                        <slot name="actions" />
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizTable from '@/Biz/Table.vue';
    import { calendar, duration, timezone } from '@/Libs/icon-class';
    import { capitalCase } from 'change-case';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizButton,
            BizFormTextarea,
            BizIcon,
            BizModalCard,
            BizTable,
        },

        inject: {
            i18n: { default: () => ({
                are_you_sure_cancel_event :'Are you sure you want to cancel this event?',
                message :'Message',
                no :'No',
            }) },
        },

        props: {
            modelValue: { type: [String, null], required: true },
            title: { type: String, default: "Reschedule Event Confirmation" },
            productName: { type: String, required: true },
            event: { type: Object, required: true },
        },

        emits: [
            'close',
            'update:modelValue'
        ],

        setup(props, { emit }) {
            return {
                icon: { calendar, duration, timezone },
                message: useModelWrapper(props, emit),
            };
        },

        methods: {
            close() {
                this.$emit('close');
            },

            capitalCase,
        },
    };
</script>
