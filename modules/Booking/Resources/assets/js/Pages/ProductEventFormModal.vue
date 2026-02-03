<template>
    <biz-modal-card
        content-class="is-huge"
        :is-close-hidden="true"
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ isNew ? (i18n?.add_new_event || 'Add New Event') : (i18n?.edit_event || 'Edit Event') }}
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="$emit('close')"
            />
        </template>

        <form
            v-if="!isBlank(form)"
            @submit.prevent="submit"
        >
            <div class="box">
                <h5 class="title is-5 mb-3">{{ i18n.event_details || 'Event Details' }}</h5>
                
            <div class="columns">
                <div class="column is-half">
                    <biz-form-input
                        v-model="form.title"
                        maxlength="255"
                        required
                            :label="i18n.event_name || 'Event Name'"
                        :message="error('title', null, formErrors)"
                    />
                </div>

                <div class="column is-half">
                    <biz-form-select
                        v-model="form.status"
                        class="is-fullwidth"
                        name="status"
                        :label="i18n.status"
                        :message="error('status', null, formErrors)"
                    >
                        <option
                            v-for="statusOption in eventStatusOptions"
                            :key="statusOption.id"
                            :value="statusOption.id"
                        >
                            {{ statusOption.value }}
                        </option>
                    </biz-form-select>
                </div>
            </div>

                    <div class="notification is-info is-light mb-4">
                        <p class="has-text-weight-semibold mb-2">📅 Pitch Schedule Constraints</p>
                        <div class="content is-small">
                            <ul class="mb-0">
                                <li v-if="pitchSchedule.dateRange && pitchSchedule.startDate">
                                    <strong>Date Range:</strong> {{ pitchSchedule.dateRange }}
                                </li>
                                <li v-if="pitchSchedule.availableDays && pitchSchedule.availableDays !== 'Not set'">
                                    <strong>Available Days:</strong> {{ pitchSchedule.availableDays }}
                                </li>
                                <li v-if="pitchSchedule.availableHours && pitchSchedule.availableHours !== 'Not set'">
                                    <strong>Typical Hours:</strong> {{ pitchSchedule.availableHours }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <biz-form-date-time
                        v-model="startedEndedAt"
                        :label="i18n.started_and_ended_at"
                        required
                        multi-calendars
                        multi-calendars-solo
                        range
                        :utc="'preserve'"
                        :enable-time-picker="false"
                        :min-date="pitchMinDate"
                        :max-date="pitchMaxDate"
                        :disabled-week-days="disabledWeekDays"
                        :message="error(['started_at', 'ended_at'], null, formErrors)"
                    />
                </div>

            <!-- Pitch Location (Read-only) -->
            <div class="notification is-light">
                <p class="has-text-weight-bold mb-2">📍 Event Location</p>
                <p class="is-size-6">
                    This event will use the Pitch's location:
                </p>
                <div class="mt-2">
                    <p v-if="product.location?.address">
                        <strong>Address:</strong> {{ product.location.address }}
                    </p>
                    <p v-if="product.location?.city">
                        <strong>City:</strong> {{ product.location.city }}
                    </p>
                    <p v-if="product.location?.country_code">
                        <strong>Country:</strong> {{ product.location.country_code }}
                    </p>
                </div>
            </div>

            <!-- Schedule Section -->
            <div class="box">
                <h5 class="title is-5 mb-3">
                    {{ i18n.schedule || 'Schedule' }}
                </h5>

                <p class="help mb-4">
                    Define when this event is available for booking. Schedule must be within the Pitch's constraints.
                </p>

                <div v-if="hasPitchWeeklyHours" class="notification is-warning is-light mb-4">
                    <p class="has-text-weight-semibold mb-2">⚠️ Pitch Schedule Hours</p>
                    <p class="is-size-7">
                        The Pitch has specific hours set for each day. Your event schedule will be validated against these constraints:
                    </p>
                    <div class="content is-small mt-2">
                        <ul class="mb-0">
                            <li v-for="(times, day) in pitchSchedule.weeklyHoursData" :key="day">
                                <strong>{{ weekdays[day] }}:</strong> 
                                <span v-for="(timeRange, idx) in times" :key="idx">
                                    {{ timeRange.started_time }}-{{ timeRange.ended_time }}
                                    <span v-if="idx < times.length - 1">, </span>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Weekly Hours -->
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            {{ i18n.weekly_hours || 'Weekly Hours' }}
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
                                            :errors="formErrors"
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

                <!-- Date Overrides -->
                <div class="card mt-4">
                    <header class="card-header">
                        <p class="card-header-title">
                            {{ i18n.date_override || 'Date Override' }}
                        </p>
                    </header>

                    <div class="card-content">
                        <p class="help mb-3">
                            {{ i18n.date_override_description || 'Add dates when your availability changes from your weekly hours' }}
                        </p>

                        <biz-button
                            class="is-info is-fullwidth mb-4"
                            type="button"
                            @click="openDateOverrideModal()"
                        >
                            {{ i18n.add_date || 'Add Date' }}
                        </biz-button>

                        <div
                            v-for="(dateOverrideBatch, batch) in dateOverrideBatches"
                            :key="batch"
                            class="box"
                        >
                            <div class="columns is-vcentered">
                                <div class="column is-5">
                                    <strong>{{ displayDates(batch) }}</strong>
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
                                            {{ i18n.unavailable || 'Unavailable' }}
                                        </biz-tag>
                                    </template>
                                </div>

                                <div class="column is-3 has-text-right">
                                    <div class="field is-grouped is-grouped-right">
                                        <div class="control">
                                            <biz-button-icon
                                                type="button"
                                                :icon="icon.edit"
                                                @click="openDateOverrideModal(batch)"
                                            />
                                        </div>
                                        <div class="control">
                                            <biz-button-icon
                                                type="button"
                                                class="is-danger"
                                                :icon="icon.remove"
                                                @click="removeDateOverride(batch)"
                                            />
                                        </div>
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

            <div class="box">
                <h5 class="title is-5 mb-3">{{ i18n.additional_info || 'Additional Information' }}</h5>
                
                <div class="columns">
                    <div class="column">
                        <biz-language-tab
                            class="is-right"
                            :locale-options="selectableLocales"
                            :selected-locale="selectedLocale"
                            @on-change-locale="onChangeLocale"
                        />
                    </div>
                </div>

                <template v-if="form.translations[selectedLocale]">
                    <biz-form-textarea
                        v-model="form.translations[ selectedLocale ].excerpt"
                        :label="i18n.short_description || 'Short Description'"
                        :placeholder="i18n.short_description || 'Short Description'"
                        rows="2"
                        maxlength="800"
                        :message="error('translations.'+selectedLocale+'.excerpt', null, formErrors)"
                    />

                    <biz-form-textarea
                        v-model="form.translations[ selectedLocale ].description"
                        :label="i18n.long_description || 'Long Description'"
                        :placeholder="i18n.long_description || 'Long Description'"
                        rows="5"
                        maxlength="65000"
                        :message="error('translations.'+selectedLocale+'.description', null, formErrors)"
                    />
                </template>
            </div>

            <!-- Date Override Modal -->
            <product-edit-modal-date-override
                v-if="isModalOpen"
                :selected-date-override-batch="selectedDateOverrideBatch"
                :pitch-date-range="pitchDateRange"
                :unused-dates="unusedDates"
                @close="closeModal()"
                @after-apply="afterApply"
            />
        </form>

        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button @click="$emit('close')">
                            {{ i18n.cancel }}
                        </biz-button>

                        <biz-button
                            class="is-link ml-1"
                            type="button"
                            @click="submit"
                        >
                            {{ isNew ? i18n.create : i18n.update }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizTag from '@/Biz/Tag.vue';
    import BizFormDateTime from '@/Biz/Form/DateTime.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import BizFormTimezone from '@/Biz/Form/Timezone.vue';
    import BizLanguageTab from '@/Biz/LanguageTab.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import ProductEditModalDateOverride from '@booking/Pages/ProductEditModalDateOverride.vue';
    import ScheduleRuleTimes from '@booking/Pages/ScheduleRuleTimes.vue';
    import { cloneDeep, find, sortBy, groupBy, map } from 'lodash';
    import { isBlank } from '@/Libs/utils';
    import { computed, ref } from 'vue';
    import { confirmLeaveProgress, success as successAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/vue3';
    import { capitalCase } from 'change-case';
    import icon from '@/Libs/icon-class';
    import moment from 'moment';

    export default {
        name: 'ProductEventFormModal',

        components: {
            BizButton,
            BizButtonIcon,
            BizCheckbox,
            BizTag,
            BizFormDateTime,
            BizFormInput,
            BizFormSelect,
            BizFormTextarea,
            BizFormTimezone,
            BizLanguageTab,
            BizModalCard,
            ProductEditModalDateOverride,
            ScheduleRuleTimes,
        },

        mixins: [
            MixinHasPageErrors,
            MixinHasModal,
        ],

        inject: {
            i18n: { 
                from: 'i18n',
                default: () => ({
                title: 'Title',
                add_new_event: 'Add new event',
                edit_event: 'Edit event',
                started_and_ended_at: 'Started at and ended at',
                excerpt: 'Excerpt',
                description: 'Description',
                cancel: 'Cancel',
                create: 'Create',
                update: 'Update',
                    status: 'Status',
                })
            },
            eventStatusOptions: { 
                from: 'eventStatusOptions',
                default: () => []
            },
        },

        props: {
            selectedEvent: { type: Object, default: () => {} },
            product: { type: Object, required: true },
            timezone: { type: String, default: 'UTC' },
            weekdays: { type: Object, required: true },
            pitchSchedule: { 
                type: Object, 
                default: () => ({
                    timezone: 'Not set',
                    dateRange: 'Not set',
                    availableDays: 'Not set',
                    availableHours: 'Not set',
                    startDate: null,
                    endDate: null,
                    availableDaysArray: [],
                    weeklyHoursData: {},
                })
            },
        },

        computed: {
            productTimezone() {
                return this.timezone || 'UTC';
            },
        },

        emits: [
            'close',
            'after-submit',
        ],

        setup(props) {
            const defaultLocale = usePage().props.defaultLanguage;
            let selectedLocale = props.locale ?? defaultLocale;

            const localeOptions = sortBy(
                usePage().props.languageOptions,
                [
                    function(locale) {
                        return locale.id != selectedLocale;
                    }
                ]
            );

            if (typeof find(localeOptions, { 'id': selectedLocale }) === 'undefined') {
                selectedLocale = defaultLocale;
            }

            return {
                capitalCase,
                defaultLocale,
                localeOptions: localeOptions,
                selectedLocale: ref(selectedLocale),
                startTime: ref({ hours: 10, minutes: 0 }),
                endTime: ref({ hours: 17, minutes: 0 }),
            };
        },

        data() {
            return {
                eventRecord: {},
                formErrors: {},
                form: {},
                icon,
                selectedDateOverrideBatch: null,
                unusedDates: [],
            };
        },

        computed: {
            isNew() {
                return !this.eventRecord.id;
            },

            isFormReady() {
                return !isBlank(this.form);
            },

            selectableLocales() {
                if (!this.eventRecord.id) {
                    return [find(this.localeOptions, ['id', this.defaultLocale])];
                }
                return this.localeOptions;
            },

            pitchDateRange() {
                if (this.form.started_at && this.form.ended_at) {
                    return [this.form.started_at, this.form.ended_at];
                }
                return [];
            },

            dateOverrideBatches() {
                if (!this.form.date_overrides) {
                    return {};
                }
                return groupBy(this.form.date_overrides, 'started_date');
            },

            dateOverrideBatchErrors() {
                const errors = {};
                // Add error handling if needed
                return errors;
            },

            pitchMinDate() {
                // Return the pitch's start date as minimum date for events
                if (this.pitchSchedule?.startDate) {
                    return new Date(this.pitchSchedule.startDate);
                }
                return null;
            },

            pitchMaxDate() {
                // Return the pitch's end date as maximum date for events
                if (this.pitchSchedule?.endDate) {
                    return new Date(this.pitchSchedule.endDate);
                }
                return null;
            },

            disabledWeekDays() {
                // Disable weekdays that are not available in the pitch schedule
                // vue-datepicker uses 0=Sunday, 1=Monday, ..., 6=Saturday
                // Our system uses 1=Monday, 2=Tuesday, ..., 7=Sunday
                
                if (!this.pitchSchedule?.availableDaysArray || this.pitchSchedule.availableDaysArray.length === 0) {
                    return []; // No restrictions if no available days defined
                }

                const availableDays = this.pitchSchedule.availableDaysArray;
                const allDays = [0, 1, 2, 3, 4, 5, 6]; // Sunday to Saturday in vue-datepicker format
                
                // Convert our format (1=Mon...7=Sun) to vue-datepicker format (0=Sun, 1=Mon...6=Sat)
                const availableDaysVueDatepicker = availableDays.map(day => {
                    if (day === 7) return 0; // Sunday
                    return day; // Monday=1, Tuesday=2, etc.
                });

                // Return days that should be disabled (all days minus available days)
                return allDays.filter(day => !availableDaysVueDatepicker.includes(day));
            },

            hasPitchWeeklyHours() {
                return this.pitchSchedule?.weeklyHoursData && 
                       Object.keys(this.pitchSchedule.weeklyHoursData).length > 0;
            },

            startedEndedAt: {
                get() {
                    if (this.form.started_at) {
                        return [
                            this.form.started_at,
                            this.form.ended_at,
                        ];
                    }
                    return [];
                },
                set(newValue) {
                    if (newValue == null) {
                        this.form.started_at = this.form.ended_at = null;
                    } else {
                        this.form.started_at = newValue[0] ?? null;
                        this.form.ended_at = newValue[1] ?? null;
                    }
                }
            },
        },

        beforeMount: async function() {
            console.log('ProductEventFormModal: beforeMount START', {
                selectedEvent: this.selectedEvent,
                product: this.product,
                timezone: this.timezone,
                pitchSchedule: this.pitchSchedule
            });

            if (this.selectedEvent?.id) {
                console.log('ProductEventFormModal: Editing existing event');
                this.eventRecord = await this.getRecord();
                this.setForm(this.selectedLocale);
            } else {
                console.log('ProductEventFormModal: Creating new event');
                const newEventData = this.newEvent();
                console.log('ProductEventFormModal: New event data:', newEventData);
                this.form = useForm(newEventData);
                console.log('ProductEventFormModal: Form initialized:', this.form);
                console.log('ProductEventFormModal: isBlank(form)?', isBlank(this.form));
            }
            
            console.log('ProductEventFormModal: beforeMount END');
        },

        methods: {
            isBlank: isBlank,

            newEvent() {
                // Initialize weekly hours for 7 days
                const weeklyHours = {};
                for (let day = 1; day <= 7; day++) {
                    weeklyHours[day] = {
                        day: day,
                        is_available: false,
                        hours: []
                    };
                }

                const event = {
                    title: null,
                    started_at: null,
                    ended_at: null,
                    translations: {},
                    // Timezone inherited from Pitch
                    timezone: this.timezone || usePage().props.timezone,
                    status: 'draft',
                    weekly_hours: weeklyHours,
                    date_overrides: [],
                    // Location inherited from Pitch (handled by backend)
                };

                event.translations[this.selectedLocale] = this.newTranslation();

                return event;
            },

            newTranslation() {
                return {
                    excerpt: null,
                    description: null,
                };
            },

            getRecord() {
                const url = route(
                    'admin.booking.products.product-events.show',
                    { product: this.product.id, product_event: this.selectedEvent.id }
                );

                return axios.get(url)
                    .then((response) => {
                        return response.data;
                    });
            },

            onChangeLocale(locale) {
                const self = this;

                if (locale == this.selectedLocale) {
                    return false;
                }

                if (this.form.isDirty) {
                    confirmLeaveProgress().then((result) => {
                        if (result.isDismissed) {
                            return false;
                        } else if (result.isConfirmed) {
                            self.setForm(locale);

                            self.selectedLocale = locale;
                        }
                    });
                } else {
                    self.setForm(locale);

                    self.selectedLocale = locale;
                }
            },

            setForm(locale) {
                const form = cloneDeep(this.eventRecord);

                form.translations = form.translations ?? {};

                form.translations[locale] = form.translations[locale] ?? this.newTranslation();

                this.form = useForm(form);
            },

            submit() {
                const self = this;
                let method = null;
                let url = null;

                if (self.form.id) {
                    method = 'put';
                    url = route('admin.booking.products.product-events.update', [self.product.id, self.form.id]);
                } else {
                    method = 'post';
                    url = route('admin.booking.products.product-events.store', self.product.id);
                }

                console.log('ProductEventFormModal: Submitting', method, url);
                console.log('ProductEventFormModal: Form data', self.form.data());

                axios[method](url, self.form.data())
                    .then((response) => {
                        console.log('ProductEventFormModal: Success response', response.data);
                        self.eventRecord = response.data.event;

                        self.setForm(self.selectedLocale);

                        successAlert(response.data.message);

                        self.$emit('after-submit');

                        self.formErrors = {};
                    })
                    .catch((error) => {
                        console.error('ProductEventFormModal: Error', error);
                        console.error('ProductEventFormModal: Error response', error.response);
                        self.formErrors = error.response.data.errors;
                    });
            },

            // Schedule methods
            addTimeRange(index) {
                this.form.weekly_hours[index].hours.push({
                    started_time: '09:00',
                    ended_time: '17:00',
                });
            },

            checkTimes(index) {
                const weeklyHour = this.form.weekly_hours[index];
                
                if (weeklyHour.is_available && weeklyHour.hours.length === 0) {
                    this.addTimeRange(index);
                }
            },

            timeRangeRemoved(index) {
                const weeklyHour = this.form.weekly_hours[index];
                
                if (weeklyHour.hours.length === 0) {
                    weeklyHour.is_available = false;
                }
            },

            // Date override methods
            openDateOverrideModal(batch = null) {
                this.selectedDateOverrideBatch = batch;
                this.openModal();
            },

            removeDateOverride(batch) {
                this.form.date_overrides = this.form.date_overrides.filter(
                    override => override.started_date !== batch
                );
            },

            afterApply(dateOverride) {
                const batch = dateOverride.started_date;
                
                // Remove existing overrides for this batch
                this.form.date_overrides = this.form.date_overrides.filter(
                    override => override.started_date !== batch
                );
                
                // Add new overrides
                if (Array.isArray(dateOverride.dates)) {
                    dateOverride.dates.forEach(date => {
                        this.form.date_overrides.push({
                            started_date: date,
                            is_available: dateOverride.is_available,
                            times: dateOverride.times || []
                        });
                    });
                } else {
                    this.form.date_overrides.push({
                        started_date: batch,
                        is_available: dateOverride.is_available,
                        times: dateOverride.times || []
                    });
                }
                
                this.closeModal();
            },

            displayDates(batch) {
                const overrides = this.dateOverrideBatches[batch];
                if (!overrides || overrides.length === 0) {
                    return batch;
                }
                
                if (overrides.length === 1) {
                    return moment(batch).format('D MMM YYYY');
                }
                
                const dates = overrides.map(o => moment(o.started_date).format('D MMM'));
                return dates.join(', ');
            },
        }
    }
</script>
