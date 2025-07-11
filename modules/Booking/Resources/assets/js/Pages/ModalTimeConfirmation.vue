<template>
    <biz-modal-card
        @close="close()"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ title }}
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
                <td><b>{{ rescheduleDateTime }}</b></td>
            </tr>
            <tr>
                <th><s><biz-icon :icon="icon.calendar" /></s></th>
                <td><s>{{ event.start_end_time }}, {{ event.date }}</s></td>
            </tr>
        </biz-table>

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
                            {{ i18n.cancel }}
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
    import { durationDateTimeText } from '@booking/Libs/event';
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
                message : 'Message',
                cancel : 'Cancel',
            }) },
        },

        props: {
            modelValue: { type: [String, null], required: true },
            title: { type: String, default: "Reschedule Event Confirmation" },
            productName: { type: String, required: true },
            event: { type: Object, required: true },
            selectedDate: { type: Object, required: true },
            selectedTime: { type: String, required: true },
        },

        emits: [
            'close',
            'update:modelValue'
        ],

        setup(props, { emit }) {
            const rescheduleDateTime = durationDateTimeText(
                props.selectedDate,
                props.selectedTime,
                props.event.duration_details.duration,
                props.event.duration_details.unit
            );

            return {
                icon: { calendar, duration, timezone },
                rescheduleDateTime,
                message: useModelWrapper(props, emit),
            };
        },

        methods: {
            close() {
                this.$emit('close');
            },
        },
    };
</script>
