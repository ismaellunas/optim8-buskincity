<template>
    <div class="columns is-multiline">
        <div
            v-for="(ruleTime, hourIdx) in timeRanges"
            :key="hourIdx"
            class="column is-full"
        >
            <div class="columns is-multiline">
                <div class="column is-9">
                    <div class="event-time-range">
                        <schedule-rule-time
                            v-model="timeRanges[ hourIdx ]"
                            :min="ruleTime.ended_time ?? null"
                        />
                    </div>
                </div>

                <div class="column is-3">
                    <biz-button-icon
                        type="button"
                        class="is-danger"
                        :icon="icon.remove"
                        @click="removeTimeRange( hourIdx )"
                    />
                </div>

                <div
                    v-if="hasErrorByIndex(`${errorKeyPrefix}.${hourIdx}`)"
                    class="help is-danger mt-0 ml-2"
                >
                    <div
                        v-for="(error, errorIdx) in errors[`${errorKeyPrefix}.${hourIdx}`]"
                        :key="errorIdx"
                    >
                        {{ error }}
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="timeRanges.length == 0"
            class="column is-full"
        >
            <slot name="emptyTimeRange" />
        </div>
    </div>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import ScheduleRuleTime from '@booking/Pages/ScheduleRuleTime.vue';
    import icon from '@/Libs/icon-class';
    import moment from 'moment';
    import { isEmpty, last } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizButtonIcon,
            ScheduleRuleTime,
        },

        props: {
            modelValue: { type: Array, required: true },
            errorKeyPrefix: { type: String, default: '' },
            errors: { type: Object, default: () => {} },
            timeFormat: { type: String, default: 'HH:mm' },
        },

        emits: [
            'time-range-removed',
            'time-range-added',
        ],

        setup(props, { emit }) {
            return {
                timeRanges: useModelWrapper(props, emit),
                icon,
                defaultTimeRange: [
                    '09:00',
                    '17:00',
                ],
            };
        },

        computed: {
            canAddTimeRange() {
                const timeRange = last(this.timeRanges);
                const maxAllowedTime = moment('23:45', this.timeFormat);

                if (!timeRange) {
                    return true;
                }

                return moment(timeRange.ended_time, this.timeFormat)
                    .isBefore(maxAllowedTime);
            },
        },

        methods: {
            removeTimeRange(index) {
                this.timeRanges.splice(index, 1);

                this.$emit('time-range-removed');
            },

            addTimeRange() {
                if (this.timeRanges.length == 0) {

                    this.timeRanges.push({
                        started_time: this.defaultTimeRange[0],
                        ended_time: this.defaultTimeRange[1],
                    });

                } else {

                    const latestTimeRange = last(this.timeRanges);

                    if (!this.canAddTimeRange) {
                        return false;
                    }

                    const maxAllowedTime = moment('23:45', this.timeFormat);

                    const startTime = moment(latestTimeRange.ended_time, this.timeFormat);

                    let started_time = startTime.clone().add(1, 'hour');
                    let ended_time = startTime.clone().add(2, 'hour');

                    if (
                        started_time.isSameOrAfter(maxAllowedTime)
                    ) {
                        started_time = startTime.clone();
                        ended_time = maxAllowedTime.clone();
                    }

                    if (
                        started_time.isSame(maxAllowedTime)
                        && ended_time.isSame(maxAllowedTime)
                    ) {
                        return false;
                    }

                    this.timeRanges.push({
                        started_time: started_time.format(this.timeFormat),
                        ended_time: ended_time.format(this.timeFormat),
                    });
                }

                this.$emit('time-range-added');
            },

            hasErrorByIndex(index) {
                return (!isEmpty(this.errors) && this.errors[index]);
            },
        },
    };
</script>
