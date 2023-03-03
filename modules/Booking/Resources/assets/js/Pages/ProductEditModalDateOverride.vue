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
                    <div class="column">
                        <schedule-rule-times
                            ref="scheduleRuleTime"
                            v-model="timeRanges"
                        >
                            <template #emptyTimeRange>
                                <biz-tag class="is-warning is-medium is-italic">
                                    Unavailable
                                </biz-tag>
                            </template>
                        </schedule-rule-times>
                    </div>

                    <div class="column is-2">
                        <biz-button-icon
                            class="is-info"
                            type="button"
                            :icon="iconAdd"
                            @click="addTime()"
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
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizDatepicker from '@/Biz/Datepicker.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizTag from '@/Biz/Tag.vue';
    import ScheduleRuleTimes from '@booking/Pages/ScheduleRuleTimes.vue';
    import moment from 'moment';
    import { add as iconAdd } from '@/Libs/icon-class';
    import { cloneDeep, difference } from 'lodash';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'ProductEditModalDateOverride',

        components: {
            BizButton,
            BizButtonIcon,
            BizModalCard,
            BizTag,
            BizDatepicker,
            ScheduleRuleTimes,
        },

        props: {
            modelValue: { type: Object, required: true },
            formatDateIso: { type: String, default: 'YYYY-MM-DD' },
        },

        emits: [
            'close',
            'after-apply',
        ],

        setup(props, { emit }) {
            const dateOverrides = useModelWrapper(props, emit);

            const dates = cloneDeep(props.modelValue.dates);

            const timeRanges = cloneDeep(props.modelValue.times);

            return {
                dateOverrides,
                dates: ref(dates),
                iconAdd,
                minDate: moment().toDate(),
                timeRanges: ref(timeRanges),
            };
        },

        computed: {
            hasDateSelected() {
                return this.dates && this.dates.length > 0;
            },
        },

        methods: {
            dateToIso(date) {
                return moment(date).format(this.formatDateIso)
            },

            addTime() {
                this.$refs.scheduleRuleTime.addTimeRange();
            },

            apply() {
                this.dateOverrides.unusedDates = difference(
                    this.dateOverrides.dates.map(this.dateToIso),
                    this.dates.map(this.dateToIso)
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
        },
    };
</script>
