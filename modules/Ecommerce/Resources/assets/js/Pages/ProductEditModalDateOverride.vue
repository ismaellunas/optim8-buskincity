<template>
    <biz-modal-card
        content-class="is-tiny"
        @close="close()"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                Date Overrides
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="$emit('close')"
            />
        </template>

        <form @submit.prevent="apply">
            <div class="field is-grouped is-grouped-centered">
                <p class="control">
                    <biz-datepicker
                        v-model="dates"
                        auto-apply
                        inline
                        month-name-format="long"
                        multi-dates
                        no-today
                        :enable-time-picker="false"
                        :min-date="minDate"
                    />
                </p>
            </div>

            <div v-show="hasDateSelected">
                <div class="columns">
                    <div class="column has-text-centered">
                        What hours are you available?
                    </div>
                </div>

                <div class="columns">
                    <div
                        v-if="timeRanges.length > 0"
                        class="column"
                    >
                        <div
                            v-for="(timeRange, index) in timeRanges"
                            :key="timeRange.uid"
                            class="columns"
                        >
                            <div class="column is-narrow">
                                <div class="event-time-range">
                                    <biz-date-time
                                        v-model="timeRanges[index].timeRange"
                                        type="time"
                                        dialog
                                        range
                                        :options="{color: 'info'}"
                                        @input="onTimeInput(timeRanges[index], $event)"
                                    />
                                </div>
                            </div>

                            <div class="column is-narrow">
                                <div class="buttons is-pulled-left">
                                    <biz-button-icon
                                        type="button"
                                        class="is-danger"
                                        :icon="icon.remove"
                                        @click="removeTimeRange(timeRanges, index)"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="column"
                    >
                        <biz-tag class="is-warning is-medium is-italic">
                            Unavailable
                        </biz-tag>
                    </div>

                    <div class="column is-2">
                        <biz-button-icon
                            class="is-info"
                            type="button"
                            :icon="icon.add"
                            @click="addTime(timeRanges)"
                        />
                    </div>
                </div>
            </div>
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
                            Apply
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
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizDateTime from '@/Biz/DateTime';
    import BizModalCard from '@/Biz/ModalCard';
    import BizTag from '@/Biz/Tag';
    import BizDatepicker from '@/Biz/Datepicker';
    import icon from '@/Libs/icon-class';
    import moment from 'moment';
    import { clone, cloneDeep, padStart, difference } from 'lodash';
    import { generateElementId, isBlank, useModelWrapper } from '@/Libs/utils';
    import { reactive, ref } from 'vue';

    export default {
        name: 'ProductEditModalDateOverride',

        components: {
            BizButton,
            BizButtonIcon,
            BizDateTime,
            BizModalCard,
            BizTag,
            BizDatepicker,
        },

        mixins: [
        ],

        props: {
            modelValue: { type: Object, required: true },
        },

        emits: [
            'close',
            'after-apply',
        ],

        setup(props, { emit }) {
            const dateOverrides = useModelWrapper(props, emit);

            let dates = cloneDeep(props.modelValue.dates);

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
                },
                dateOverrides,
                dates: ref(dates),
                timeRanges: ref(timeRanges),
                minDate: moment().toDate(),
            };
        },

        computed: {
            hasDateSelected() {
                return ! (typeof this.dates == undefined || this.dates == null);
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

                this.dateOverrides.unusedDates = difference(
                    this.dateOverrides.dates.map((date) => moment(date).format('YYYY-MM-DD')),
                    this.dates.map((date) => moment(date).format('YYYY-MM-DD'))
                );

                this.dateOverrides.times = this.timeRanges;
                this.dateOverrides.dates = this.dates;

                this.dateOverrides.isAvailable = this.timeRanges.length > 0;

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
        },
    };
</script>
