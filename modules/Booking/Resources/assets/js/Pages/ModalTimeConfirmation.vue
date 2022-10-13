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
                <th><biz-icon :icon="bookingIcon.duration" /></th>
                <td>{{ event.duration }}</td>
            </tr>
            <tr>
                <th><biz-icon :icon="bookingIcon.timezone" /></th>
                <td>{{ event.timezone }}</td>
            </tr>
            <tr>
                <th><biz-icon :icon="bookingIcon.calendar" /></th>
                <td><b>{{ rescheduleDateTime }}</b></td>
            </tr>
            <tr>
                <th><s><biz-icon :icon="bookingIcon.calendar" /></s></th>
                <td><s>{{ event.start_end_time }}, {{ event.date }}</s></td>
            </tr>
        </biz-table>

        <biz-form-textarea
            v-model="message"
            label="Message"
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
                            Cancel
                        </biz-button>

                        <slot name="actions" />
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import BizIcon from '@/Biz/Icon';
    import BizModalCard from '@/Biz/ModalCard';
    import BizTable from '@/Biz/Table';
    import bookingIcon from '@booking/Libs/booking-icon';
    import { computed } from 'vue';
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
                bookingIcon,
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
