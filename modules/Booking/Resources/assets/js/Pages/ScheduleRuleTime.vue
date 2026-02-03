<template>
    <biz-datepicker
        v-model="times"
        auto-apply
        minutes-grid-increment="15"
        minutes-increment="15"
        range
        time-picker
        no-hours-overlay
        no-minutes-overlay
        multi-calendar
        input-class-name="input"
        :clearable="false"
        :min-time="pitchMinTime"
        :max-time="pitchMaxTime"
    />
</template>

<script>
    import BizDatepicker from '@/Biz/Datepicker.vue';
    import moment from 'moment';
    import { padStart } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizDatepicker,
        },

        props: {
            modelValue: { type: Object, default: () => {} },
            defaultTimes: { type: Array, default: () => [] },
            pitchTimeRanges: { type: Array, default: () => [] },
        },

        setup(props, { emit }) {
            return {
                timeRanges: useModelWrapper(props, emit),
            };
        },

        computed: {
            startedTimeParts() {
                return this.timeRanges.started_time.split(':');
            },

            endedTimeParts() {
                return this.timeRanges.ended_time.split(':');
            },

            pitchMinTime() {
                // Get the earliest start time from the pitch time ranges
                if (!this.pitchTimeRanges || this.pitchTimeRanges.length === 0) {
                    return null;
                }

                const minTime = this.pitchTimeRanges.reduce((min, range) => {
                    const [hours, minutes] = range.started_time.split(':');
                    if (!min) return { hours: parseInt(hours), minutes: parseInt(minutes) };
                    
                    const currentHours = parseInt(hours);
                    const currentMinutes = parseInt(minutes);
                    
                    if (currentHours < min.hours || (currentHours === min.hours && currentMinutes < min.minutes)) {
                        return { hours: currentHours, minutes: currentMinutes };
                    }
                    return min;
                }, null);

                return minTime;
            },

            pitchMaxTime() {
                // Get the latest end time from the pitch time ranges
                if (!this.pitchTimeRanges || this.pitchTimeRanges.length === 0) {
                    return null;
                }

                const maxTime = this.pitchTimeRanges.reduce((max, range) => {
                    const [hours, minutes] = range.ended_time.split(':');
                    if (!max) return { hours: parseInt(hours), minutes: parseInt(minutes) };
                    
                    const currentHours = parseInt(hours);
                    const currentMinutes = parseInt(minutes);
                    
                    if (currentHours > max.hours || (currentHours === max.hours && currentMinutes > max.minutes)) {
                        return { hours: currentHours, minutes: currentMinutes };
                    }
                    return max;
                }, null);

                return maxTime;
            },

            times: {
                get() {
                    return [
                        {
                            hours: this.startedTimeParts[0],
                            minutes: this.startedTimeParts[1],
                        },
                        {
                            hours: this.endedTimeParts[0],
                            minutes: this.endedTimeParts[1],
                        }
                    ];
                },

                set(times) {
                    this.timeRanges.started_time = (
                        padStart(times[0].hours, 2, "0") + ":" +
                        padStart(times[0].minutes, 2, "0")
                    );

                    this.timeRanges.ended_time = (
                        padStart(times[1].hours, 2, "0") + ":" +
                        padStart(times[1].minutes, 2, "0")
                    );
                },
            },
        },
    };
</script>
