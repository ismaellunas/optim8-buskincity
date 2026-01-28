<template>
    <div class="booking-time columns is-multiline is-mobile">
        <div class="column is-6-desktop is-6-tablet is-12-mobile">
            <biz-field class="is-grouped is-grouped-centered">
                <p class="control">
                    <biz-datepicker
                        v-model="form.date"
                        auto-apply
                        hide-offset-dates
                        inline
                        month-name-format="long"
                        no-today
                        prevent-min-max-navigation
                        :allowed-dates="allowedDates"
                        :disabled="isCalendarDisabled"
                        :enable-time-picker="false"
                        :max-date="maxDate"
                        :min-date="minDate"
                        :month-change-on-scroll="false"
                        :year-range="yearRange"
                        @update-month-year="handleMonthYear"
                    />
                </p>
            </biz-field>

            <biz-field class="is-grouped is-grouped-centered">
                <biz-form-field-horizontal class="timezone-info">
                    <template #label>
                        Timezone
                    </template>

                    <p class="control is-expanded">
                        <input
                            class="input"
                            type="text"
                            :value="timezone"
                            readonly
                            disabled
                        >
                    </p>
                </biz-form-field-horizontal>
            </biz-field>
        </div>

        <div
            id="reschedule-available-time-list-wrapper"
            class="column is-6-desktop is-6-tablet is-12-mobile"
        >
            <div
                id="reschedule-available-time-list"
                class="columns is-multiline"
            >
                <div
                    v-for="(time, index) in availableTimes"
                    :key="index"
                    class="column is-full py-1"
                >
                    <div class="buttons">
                        <biz-button
                            class="px-5"
                            type="button"
                            :class="getTimeClasses(index, time)"
                            @click="toggleSlot(time, index)"
                        >
                            {{ time }}
                        </biz-button>

                        <biz-button
                            v-if="!multiSelect && isIndexSelected(index)"
                            class="button is-link px-5"
                            type="button"
                            @click="confirmTime(time)"
                        >
                            Confirm
                        </biz-button>
                    </div>
                </div>
                <div v-if="hasLoadedTimes && !availableTimes.length" class="column is-full">
                    <p class="has-text-grey has-text-centered">
                        No available times for this date.
                    </p>
                </div>
            </div>

            <div v-if="multiSelect && selectedSlots.length" class="mt-4">
                <p class="has-text-weight-bold mb-2">Selected slots</p>
                <div class="columns is-multiline">
                    <div
                        v-for="(slot, idx) in selectedSlots"
                        :key="slot.key"
                        class="column is-full py-1"
                    >
                        <div class="buttons">
                            <biz-button class="is-static">
                                {{ slot.label }}
                            </biz-button>
                            <biz-button
                                class="is-danger"
                                type="button"
                                @click="removeSlot(idx)"
                            >
                                Remove
                            </biz-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizDatepicker from '@/Biz/Datepicker.vue';
    import BizField from '@/Biz/Field.vue';
    import BizFormFieldHorizontal from '@/Biz/Form/FieldHorizontal.vue';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import moment from 'moment';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizButton,
            BizDatepicker,
            BizField,
            BizFormFieldHorizontal,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            allowedDatesRoute: { type: String, required: true },
            availableTimesParam: { type: Object, required: true },
            availableTimesRoute: { type: String, required: true },
            maxDate: { type: String, required: true },
            minDate: { type: String, required: true },
            modelValue: { type: Object, required: true },
            productId: { type: [Number, String, null], default: null },
            productEventId: { type: [Number, String, null], default: null },
            eventId: { type: [Number, String, null], default: null },
            timezone: { type: String, default: 'GMT' },
            multiSelect: { type: Boolean, default: false },
        },

        emits: [
            'on-time-confirmed'
        ],

        setup(props, { emit }) {
            return {
                allowedDates: ref([]),
                availableTimes: ref([]),
                form: useModelWrapper(props, emit),
                hasLoadedTimes: ref(false),
                isCalendarDisabled: ref(false),
                selectedIndex: ref(null),
                yearRange: [
                    moment(props.minDate).year(),
                    moment(props.maxDate).year(),
                ],
            };
        },

        watch: {
            'form.date': function (newVal, oldVal) {
                this.resetSelectedIndex();
                this.getAvailableTimes();
            },
        },

        async mounted() {
            const minDate = moment(this.minDate);

            if (this.multiSelect) {
                this.ensureSelectedSlots();
            }

            this.allowedDates = await this.getAllowedDates(minDate.month() + 1, minDate.year());
        },

        methods: {
            ensureSelectedSlots() {
                if (!this.form.selected_slots) {
                    this.form.selected_slots = [];
                }
            },

            getAvailableTimes() {
                if (! this.form.date) {
                    this.availableTimes = [];
                }

                const self = this;
                const params = {
                    ...this.availableTimesParam,
                    ...{date: moment(this.form.date).format('YYYY-MM-DD')}
                };

                self.onStartLoadingOverlay();
                self.isCalendarDisabled = true;
                self.hasLoadedTimes = false;

                axios.get(
                    route(self.availableTimesRoute, params),
                ).then((response) => {
                    self.availableTimes = response.data;
                    self.hasLoadedTimes = true;
                }).catch(() => {
                    self.availableTimes = [];
                    self.hasLoadedTimes = true;
                }).then(() => {
                    self.onEndLoadingOverlay();
                    self.isCalendarDisabled = false;
                });
            },

            isIndexSelected(index) {
                return this.selectedIndex == index;
            },

            resetSelectedIndex() {
                this.selectedIndex = null;
            },

            toggleSelectedIndex(index) {
                if (this.selectedIndex == index) {
                    this.resetSelectedIndex();
                } else {
                    this.selectedIndex = index;
                }
            },

            toggleSlot(time, index) {
                if (this.multiSelect) {
                    this.toggleSlotSelection(time);
                    return;
                }

                this.toggleSelectedIndex(index);
            },

            toggleSlotSelection(time) {
                if (!this.form.date) {
                    return;
                }

                this.ensureSelectedSlots();

                const date = moment(this.form.date).format('YYYY-MM-DD');
                const existingIndex = this.form.selected_slots.findIndex(
                    (slot) => slot.date === date && slot.time === time
                );

                if (existingIndex > -1) {
                    this.form.selected_slots.splice(existingIndex, 1);
                } else {
                    this.form.selected_slots.push({ date, time });
                }
            },

            getTimeClasses(index, time) {
                if (this.multiSelect) {
                    const date = this.form.date ? moment(this.form.date).format('YYYY-MM-DD') : null;
                    const isSelected = date
                        ? (this.form.selected_slots ?? []).some(
                            (slot) => slot.date === date && slot.time === time
                        )
                        : false;

                    return {
                        'is-danger': isSelected,
                        'has-text-weight-bold': isSelected,
                    };
                }

                return {
                    'is-danger': this.isIndexSelected(index),
                    'has-text-weight-bold': this.isIndexSelected(index),
                };
            },

            confirmTime(time) {
                this.form.time = time;
                this.$emit('on-time-confirmed');
            },

            async getAllowedDates(month, year) {
                const self = this;

                const url = route(this.allowedDatesRoute, {
                    ...(this.eventId
                        ? { event: this.eventId }
                        : { product: this.productId, product_event_id: this.productEventId }),
                    month: month,
                    year: year,
                });

                self.isCalendarDisabled = true;

                try {
                    const response =  await axios.get(url);
                    return response.data;
                } catch (error) {
                    return [];
                } finally {
                    self.isCalendarDisabled = false;
                }
            },

            async handleMonthYear({instance, month, year}) {
                this.allowedDates = await this.getAllowedDates((month + 1), year);
            },

            removeSlot(index) {
                this.ensureSelectedSlots();
                this.form.selected_slots.splice(index, 1);
            },
        },

        computed: {
            selectedSlots() {
                return (this.form.selected_slots ?? [])
                    .map((slot) => {
                        const dateLabel = moment(slot.date).format('MMM D, YYYY');
                        return {
                            key: `${slot.date}-${slot.time}`,
                            label: `${dateLabel} ${slot.time}`,
                        };
                    })
                    .sort((a, b) => a.key.localeCompare(b.key));
            },
        },
    };
</script>
