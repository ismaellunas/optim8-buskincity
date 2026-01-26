<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
            :bags="[eventErrorBag]"
        />

        <div class="box">
            <div class="columns">
                <div class="column">
                    <h5 class="title is-5 mb-2">
                        {{ productEvent.title }}
                    </h5>
                    <p class="is-size-7 has-text-grey">
                        {{ displayEventRange }}
                    </p>
                </div>
                <div class="column has-text-right">
                    <biz-button-link
                        :href="route('admin.booking.products.edit', product.id)"
                        class="is-link is-light"
                    >
                        {{ i18n.cancel }}
                    </biz-button-link>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="box">
                <h5 class="title is-5 mb-2">
                    {{ i18n.schedule }}
                </h5>

                <hr class="mt-0">

                <biz-form-timezone
                    v-model="form.timezone"
                    :label="i18n.timezone"
                    :message="error('timezone', eventErrorBag)"
                    :tooltip-message="i18n.tips.timezone"
                    required
                />

                <hr>

                <div class="columns">
                    <div class="column">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                    {{ i18n.weekly_hours }}

                                    <biz-tooltip
                                        class="ml-1"
                                        :message="i18n.tips.weekly_hours"
                                    />
                                </p>
                            </header>
                            <div class="card-content">
                                <div class="columns is-multiline">
                                    <div
                                        v-for="(weekday, index) in weekdays"
                                        :key="index"
                                        class="column is-full"
                                    >
                                        <div class="columns">
                                            <div class="column is-2">
                                                <biz-checkbox
                                                    v-model:checked="form.weekly_hours[index].is_available"
                                                    @change="checkTimes(index)"
                                                >
                                                    &nbsp;{{ weekday }}
                                                </biz-checkbox>
                                            </div>
                                            <div class="column">
                                                <schedule-rule-times
                                                    :ref="'scheduleRuleTime_'+index"
                                                    v-model="form.weekly_hours[index].hours"
                                                    :errors="eventErrors"
                                                    :error-key-prefix="`weekly_hours.${index}.hours`"
                                                    @time-range-removed="timeRangeRemoved(index)"
                                                />
                                            </div>
                                            <div class="column is-2">
                                                <biz-button-icon
                                                    type="button"
                                                    :icon="icon.add"
                                                    @click="addTimeRange(index)"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                    {{ i18n.date_override }}

                                    <biz-tooltip
                                        class="ml-1"
                                        :message="i18n.tips.date_override"
                                    />
                                </p>
                            </header>

                            <div class="card-content">
                                <div class="columns is-multiline">
                                    <div class="column is-full has-text-centered">
                                        {{ i18n.date_override_description }}
                                    </div>

                                    <div class="column is-full has-text-centered">
                                        <biz-button
                                            class="is-info is-fullwidth"
                                            type="button"
                                            @click="openDateOverrideModal()"
                                        >
                                            {{ i18n.add_date }}
                                        </biz-button>
                                    </div>

                                    <div class="column is-full">
                                        <div
                                            v-for="(dateOverrideBatch, batch) in dateOverrideBatches"
                                            :key="batch"
                                            class="columns is-multiline"
                                        >
                                            <div class="column is-5">
                                                {{ displayDates(batch) }}
                                            </div>

                                            <div class="column is-4 has-text-centered">
                                                <div
                                                    v-if="dateOverrideBatch[0].is_available"
                                                    class="columns is-multiline"
                                                >
                                                    <div
                                                        v-for="(time, timeIdx) in dateOverrideBatch[0].times"
                                                        :key="timeIdx"
                                                        class="column is-full"
                                                    >
                                                        {{ time.started_time.substr(0, 5) }} - {{ time.ended_time.substr(0, 5) }}
                                                    </div>
                                                </div>

                                                <template v-else>
                                                    <biz-tag class="is-warning is-medium is-italic">
                                                        {{ i18n.unavailable }}
                                                    </biz-tag>
                                                </template>
                                            </div>

                                            <div class="column is-3 has-text-right">
                                                <div class="field is-grouped is-grouped-right">
                                                    <div class="control">
                                                        <biz-button-icon
                                                            type="button"
                                                            class="is-danger"
                                                            :icon="icon.remove"
                                                            @click="removeDateOverride(batch)"
                                                        />
                                                    </div>
                                                    <div class="control">
                                                        <biz-button-icon
                                                            type="button"
                                                            :icon="icon.edit"
                                                            @click="openDateOverrideModal(batch)"
                                                        />
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                v-if="dateOverrideBatchErrors[batch]"
                                                class="column is-full"
                                            >
                                                <p
                                                    v-for="(error, errorIdx) in dateOverrideBatchErrors[batch]"
                                                    :key="errorIdx"
                                                    class="help is-danger has-text-centered"
                                                >
                                                    {{ error }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="field is-grouped is-grouped-right">
                    <div class="control">
                        <biz-button class="is-link">
                            {{ i18n.update }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </form>

        <product-edit-modal-date-override
            v-if="isModalOpen"
            id="product-event-date-override-modal"
            v-model="selectedDateOverrideBatch"
            @close="closeModal()"
            @after-apply="afterApply"
        />
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormTimezone from '@/Biz/Form/Timezone.vue';
    import BizTag from '@/Biz/Tag.vue';
    import BizTooltip from '@/Biz/Tooltip.vue';
    import ProductEditModalDateOverride from './ProductEditModalDateOverride.vue';
    import ScheduleRuleTimes from '@booking/Pages/ScheduleRuleTimes.vue';
    import icon from '@/Libs/icon-class';
    import moment from 'moment';
    import { cloneDeep, forEach, groupBy, intersection, remove, sortBy, uniq } from 'lodash';
    import { computed, ref } from 'vue';
    import { useForm } from '@inertiajs/vue3';
    import { generateElementId } from '@/Libs/utils';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizCheckbox,
            BizErrorNotifications,
            BizFormTimezone,
            BizTag,
            BizTooltip,
            ProductEditModalDateOverride,
            ScheduleRuleTimes,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
            MixinHasModal,
        ],

        layout: AppLayout,

        props: {
            product: { type: Object, required: true },
            productEvent: { type: Object, required: true },
            scheduleTimezone: { type: String, required: true },
            weekdays: { type: Object, required: true },
            weeklyHours: { type: Object, required: true },
            dateOverrides: { type: Array, required: true },
            i18n: { type: Object, default: () => {} },
        },

        setup(props) {
            const form = {
                timezone: props.scheduleTimezone,
                weekly_hours: props.weeklyHours,
                date_overrides: cloneDeep(props.dateOverrides),
            };

            return {
                eventErrorBag: 'productEventSchedule',
                eventErrors: ref({}),
                form: useForm(form),
                icon,
            };
        },

        data() {
            return {
                selectedDateOverrideBatch: null,
                unusedDates: [],
            };
        },

        computed: {
            displayEventRange() {
                const format = 'D MMM YYYY HH:mm';
                return `${moment(this.productEvent.started_at).format(format)} - ${moment(this.productEvent.ended_at).format(format)}`;
            },

            dateOverrideBatches() {
                const self = this;

                const errorBag = this.eventErrorBag;

                const dateOverrideBatches = [];

                const dateOverrides = this.form.date_overrides.map((dateOverride, index) => {
                    const errors = [];

                    errors.push(
                        self.error(`date_overrides.${index}.started_date`, errorBag),
                    );

                    dateOverride.times.forEach((time, timeIdx) => {
                        errors.push(
                            self.error(`date_overrides.${index}.times.${timeIdx}.started_time`, errorBag),
                            self.error(`date_overrides.${index}.times.${timeIdx}.ended_time`, errorBag),
                            self.error(`date_overrides.${index}.times.${timeIdx}`, errorBag),
                        );
                    });

                    dateOverride.errors = errors.filter(Boolean);

                    return dateOverride;
                });

                sortBy(dateOverrides, ['started_date'])
                    .filter((dateOverride) => !self.unusedDates.includes(dateOverride.started_date))
                    .forEach(function(rawDateOverride, index) {

                    const dateOverride = cloneDeep(rawDateOverride);

                    if (index == 0) {
                        dateOverride.batch = generateElementId();

                    } else {

                        const latestDateOverrideBatch = dateOverrideBatches[index - 1];

                        const nextDate = moment(latestDateOverrideBatch.started_date)
                            .add(1, 'days')
                            .format('YYYY-MM-DD');

                        const fnTimeRange = (time) => time.started_time + ' - ' + time.ended_time;

                        let isSimilar = false;

                        if (dateOverride.started_date == nextDate) {
                            if (
                                !latestDateOverrideBatch.is_available
                                && !dateOverride.is_available
                            ) {
                                isSimilar = true;
                            } else if (
                                latestDateOverrideBatch.is_available
                                && dateOverride.is_available
                            ) {
                                const latestTimeRanges = latestDateOverrideBatch.times.map(fnTimeRange);
                                const dateTimeRanges = dateOverride.times.map(fnTimeRange);

                                isSimilar = intersection(latestTimeRanges, dateTimeRanges).length == latestTimeRanges.length;
                            }
                        }

                        if (isSimilar) {
                            dateOverride.batch = latestDateOverrideBatch.batch;
                        } else {
                            dateOverride.batch = generateElementId();
                        }
                    }

                    dateOverrideBatches.push(dateOverride);
                });

                return groupBy(dateOverrideBatches, (dateOverride) => dateOverride.batch);
            },

            dateOverrideBatchErrors() {
                const errors = {};

                forEach(this.dateOverrideBatches, function (dateOverrideBatch, batch) {
                    let dateOverrideErrors = [];

                    dateOverrideBatch.forEach(function (dateOverride) {
                        dateOverrideErrors = dateOverrideErrors.concat(dateOverride.errors);
                    });

                    errors[batch] = uniq(dateOverrideErrors);
                });

                return errors;
            },
        },

        methods: {
            submit() {
                const self = this;
                const url = route('admin.booking.products.product-events.schedule.update', {
                    product: this.product.id,
                    productEvent: this.productEvent.id,
                });

                this.form
                    .transform((data) => ({
                        ...data,
                        _method: 'put',
                    }))
                    .post(url, {
                        errorBag: self.eventErrorBag,
                        onStart: () => self.onStartLoadingOverlay(),
                        onSuccess: (page) => {
                            self.form.date_overrides = cloneDeep(page.props.dateOverrides ?? self.form.date_overrides);
                            self.eventErrors = {};
                        },
                        onError: (errors) => {
                            self.eventErrors = errors;
                        },
                        onFinish: () => self.onEndLoadingOverlay(),
                    });
            },

            addTimeRange(index) {
                const scheduleRuleTime = this.$refs['scheduleRuleTime_' + index][0];
                scheduleRuleTime.addTimeRange();

                if (this.form.weekly_hours[index].hours.length > 0) {
                    this.form.weekly_hours[index].is_available = true;
                }

                this.eventErrors = {};
            },

            openDateOverrideModal(batch) {
                const self = this;

                self.selectedDateOverrideBatch = {
                    batch: null,
                    dates: [],
                    times: [],
                    isAvailable: false,
                    unusedDates: [],
                };

                if (batch) {
                    const dateOverrideBatch = this.dateOverrideBatches[batch];

                    let dates = [];

                    dateOverrideBatch.forEach((date) => {
                        dates.push(moment(date.started_date).toDate());
                    });

                    let times = dateOverrideBatch[0].times.map((time) => {
                        return {
                            started_time: time.started_time,
                            ended_time: time.ended_time,
                        };
                    });

                    self.selectedDateOverrideBatch.batch = batch;
                    self.selectedDateOverrideBatch.dates = dates;
                    self.selectedDateOverrideBatch.times = times;
                    self.selectedDateOverrideBatch.isAvailable = dateOverrideBatch[0].is_available;
                    self.selectedDateOverrideBatch.unusedDates = dateOverrideBatch[0].unusedDates ?? [];
                }

                self.openModal();
            },

            closeModal() {
                this.selectedDateOverrideBatch = null;
                this.isModalOpen = false;
            },

            afterApply(dateOverrideBatch) {
                this.applyDateOverrides(dateOverrideBatch);
                this.closeModal();
            },

            applyDateOverrides(dateOverrideBatch) {
                if (dateOverrideBatch.dates.length == 0) {
                    return;
                }

                const newDateOverrides = [];
                const dates = dateOverrideBatch.dates;
                const hasTimes = dateOverrideBatch.times.length > 0;

                dates.forEach((date, index) => {
                    const startedDate = moment(date).format('YYYY-MM-DD');
                    const dateOverride = {
                        started_date: startedDate,
                        is_available: dateOverrideBatch.isAvailable,
                        times: [],
                    };

                    if (dateOverrideBatch.isAvailable && hasTimes) {
                        dateOverride.times = dateOverrideBatch.times.map((time) => {
                            return {
                                started_time: time.started_time,
                                ended_time: time.ended_time,
                            };
                        });
                    }

                    newDateOverrides.push(dateOverride);
                });

                if (dateOverrideBatch.batch) {
                    const batchDates = this.dateOverrideBatches[dateOverrideBatch.batch];
                    const batchDatesString = batchDates.map(date => date.started_date);

                    remove(this.form.date_overrides, function (dateOverride) {
                        return batchDatesString.includes(dateOverride.started_date);
                    });
                }

                this.form.date_overrides = this.form.date_overrides.concat(newDateOverrides);
                this.eventErrors = {};
            },

            removeDateOverride(batch) {
                const dateOverrideBatch = this.dateOverrideBatches[batch];

                dateOverrideBatch.forEach((date) => {
                    this.unusedDates.push(date.started_date);
                });
            },

            displayDates(batch) {
                if (!batch) {
                    return '';
                }

                const dateOverrideBatch = this.dateOverrideBatches[batch];
                const startedDate = moment(dateOverrideBatch[0].started_date);
                const endedDate = moment(dateOverrideBatch[dateOverrideBatch.length - 1].started_date);
                const format = 'D MMM YYYY';

                return startedDate.isSame(endedDate)
                    ? startedDate.format(format)
                    : startedDate.format(format) + ' - ' + endedDate.format(format);
            },

            checkTimes(index) {
                const day = this.form.weekly_hours[index];

                if (!day.is_available) {
                    day.hours = [];
                } else if (day.hours.length == 0) {
                    this.addTimeRange(index);
                }
            },

            timeRangeRemoved(index) {
                const day = this.form.weekly_hours[index];

                if (day.hours.length == 0) {
                    day.is_available = false;
                }
            },
        },
    };
</script>
