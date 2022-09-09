<template>
    <biz-modal-card
        @close="close()"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                Event Confirmation
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="$emit('close')"
            />
        </template>

        <form @submit.prevent="reschedule">
        </form>

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

                        <biz-button
                            class="is-info ml-1"
                            type="button"
                            @click="apply"
                        >
                            Submit
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
    import BizModalCard from '@/Biz/ModalCard';

    export default {
        components: {
            BizButton,
            BizModalCard,
        },

        mixins: [
        ],

        props: {
            modelValue: { type: Object, required: true },
            title: { type}
        },

        emits: [
            'close',
            'submit',
        ],

        setup(props, { emit }) {
            const dateOverride = useModelWrapper(props, emit);

            let dateRanges = [];

            if (! isBlank(props.modelValue.started_date)) {
                dateRanges.push(new Date(props.modelValue.started_date));

                if (! isBlank(props.modelValue.ended_date)) {
                    dateRanges.push(new Date(props.modelValue.ended_date));
                } else {
                    dateRanges.push(new Date(props.modelValue.started_date));
                }
            }

            let timeRanges = [];

            if (! isBlank(props.modelValue.times)) {
                timeRanges = cloneDeep(props.modelValue.times);

                timeRanges.forEach((time) => {
                    let startTimeText = time.started_time.split(':');
                    let endTimeText = time.ended_time.split(':');

                    time.timeRange = [
                        new Date(0,0,0,startTimeText[0],startTimeText[1]),
                        new Date(0,0,0,endTimeText[0],endTimeText[1]),
                    ];
                });
            }

            return {
                icon,
                defaultTime: {
                    started_time: "09:00",
                    ended_time: "17:00",
                    timeRange: [
                        new Date(0,0,0,9,0),
                        new Date(0,0,0,17,0),
                    ],
                    uid: generateElementId(),
                },
                dateRanges: reactive(dateRanges),
                timeRanges: reactive(timeRanges),
                dateOverride,
            };
        },

        computed: {
            hasDateSelected() {
                return ! (typeof this.dateRanges == undefined || this.dateRanges == null);
            }
        },

        methods: {
            addTime(times) {
                times.push(cloneDeep(this.defaultTime));
            },

            removeTimeRange(hours, index) {
                hours.splice(index, 1);
            },

            apply() {
                this.timeRanges.forEach((time) => {
                    delete time.timeRange;
                });

                this.dateOverride.times = this.timeRanges;

                this.setDateOverrideFromDateRange(this.dateRanges);

                this.dateOverride.is_available = this.timeRanges.length > 0;

                this.$emit('close');
                this.$emit('after-apply');
            },

            close() {
                this.$emit('close');
            },

            onTimeInput(hour, times) {
                ['started_time', 'ended_time'].forEach((timeKey, index) => {
                    hour[timeKey] = (
                        padStart(times[index].getHours(), 2 , '0')
                        + ':'
                        + padStart(times[index].getMinutes(), 2, '0')
                    );
                });
            },

            dateToYmd(date) {
                return date.getFullYear()
                    + '-' + padStart((date.getMonth() + 1 ), 2, 0)
                    + '-' + padStart(date.getDate(), 2, 0);
            },

            setDateOverrideFromDateRange(dateRanges) {
                this.dateOverride.started_date = this.dateToYmd(dateRanges[0]);
                this.dateOverride.displayDates = moment(this.dateOverride.started_date).format('D MMM YYYY');

                if (dateRanges[1]) {
                    const endedDate = this.dateToYmd(dateRanges[1]);

                    if (this.dateOverride.started_date == endedDate) {
                        this.dateOverride.ended_date = null;
                    } else {
                        this.dateOverride.ended_date = endedDate;
                        this.dateOverride.displayDates += ' - ' + moment(this.dateOverride.ended_date).format('D MMM YYYY');
                    }
                } else {
                    this.dateOverride.ended_date = null;
                }
            }
        },
    };
</script>
