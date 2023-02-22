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
