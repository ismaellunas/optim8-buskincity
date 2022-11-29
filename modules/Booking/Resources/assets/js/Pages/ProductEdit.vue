<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
            :bags="['default']"
        />

        <biz-provide-inject-tabs
            v-model="activeTab"
            class="is-boxed"
        >
            <biz-provide-inject-tab
                title="Product"
                class="mb-6"
            >
                <form
                    method="post"
                    @submit.prevent="submit"
                >
                    <div class="box">
                        <product-form
                            ref="product_form"
                            v-model="form"
                            :role-options="roleOptions"
                            :status-options="statusOptions"
                            :gallery="product.gallery"
                            :rules="rules"
                            :image-mimes="imageMimes"
                        />

                        <hr>

                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button-link
                                    :href="route(baseRouteName+'.index')"
                                    class="is-link is-light"
                                >
                                    Cancel
                                </biz-button-link>
                            </div>
                            <div class="control">
                                <biz-button class="is-link">
                                    Update
                                </biz-button>
                            </div>
                        </div>
                    </div>
                </form>
            </biz-provide-inject-tab>

            <biz-provide-inject-tab
                id="product-event-tab"
                title="Event"
                class="mb-6"
            >
                <form @submit.prevent="eventSubmit">
                    <div class="box">
                        <h5 class="title is-5 mb-2">
                            Details
                        </h5>

                        <hr class="mt-0">

                        <div class="columns">
                            <div class="column is-6">
                                <biz-form-select
                                    v-model="eventForm.duration"
                                    label="Duration"
                                    :message="error('duration', 'eventForm')"
                                    has-addons
                                    required
                                    is-fullwidth
                                >
                                    <option
                                        v-for="durationOption in eventDurationOptions"
                                        :key="durationOption.id"
                                        :value="durationOption.id"
                                    >
                                        {{ durationOption.value }}
                                    </option>

                                    <template #afterInput>
                                        <p class="control">
                                            <a class="button is-static">
                                                minute(s)
                                            </a>
                                        </p>
                                    </template>
                                </biz-form-select>
                            </div>

                            <div class="column is-6">
                                <biz-form-number-addons
                                    v-model="eventForm.bookable_date_range"
                                    label="Bookable date range (Calendar days into the future)"
                                    max="365"
                                    min="0"
                                    required
                                    :message="error('bookable_date_range', 'eventForm')"
                                >
                                    <template #afterInput>
                                        <p class="control">
                                            <a class="button is-static">
                                                day(s)
                                            </a>
                                        </p>
                                    </template>
                                </biz-form-number-addons>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <biz-form-textarea
                                    v-model="eventForm.location.address"
                                    label="Address"
                                    placeholder="Address"
                                    rows="5"
                                    maxlength="500"
                                    :message="error('location.address', 'eventForm')"
                                />
                            </div>

                            <div class="column is-5">
                                <div class="columns is-multiline">
                                    <div class="column is-12">
                                        <biz-form-input
                                            v-model="eventForm.location.latitude"
                                            label="Latitude"
                                            :message="error('location.latitude', 'eventForm')"
                                        />
                                    </div>
                                    <div class="column is-12">
                                        <biz-form-input
                                            v-model="eventForm.location.longitude"
                                            label="Longitude"
                                            :message="error('location.longitude', 'eventForm')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="column is-1">
                                <div class="field">
                                    <label class="label">
                                        Map
                                    </label>
                                    <span class="control">
                                        <biz-button-icon
                                            type="button"
                                            class="is-primary"
                                            :icon="icon.locationMark"
                                            @click="toggleMap"
                                        />
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="columns is-multiline">
                            <div
                                v-if="isMapOpen"
                                class="column is-8"
                            >
                                <div class="card">
                                    <div class="card-content p-2">
                                        <biz-gmap-marker
                                            v-model="eventForm.location"
                                            :api-key="googleApiKey"
                                            :init-position="geoLocation"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box">
                        <h5 class="title is-5 mb-2">
                            Schedule
                        </h5>

                        <hr class="mt-0">

                        <biz-form-select
                            v-model="eventForm.timezone"
                            label="Timezone"
                            :message="error('timezone', 'eventForm')"
                            required
                        >
                            <option
                                v-for="timezoneOption in timezoneOptions"
                                :key="timezoneOption.id"
                                :value="timezoneOption.id"
                            >
                                {{ timezoneOption.value }}
                            </option>
                        </biz-form-select>

                        <hr class="mt-0">

                        <div class="columns">
                            <div class="column">
                                <div class="card">
                                    <header class="card-header">
                                        <p class="card-header-title">
                                            Weekly hours
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
                                                            v-model:checked="eventForm.weekly_hours[index].is_available"
                                                            @change="checkTimes(index)"
                                                        >
                                                            &nbsp;{{ weekday }}
                                                        </biz-checkbox>
                                                    </div>
                                                    <div class="column">
                                                        <schedule-rule-times
                                                            :ref="'scheduleRuleTime_'+index"
                                                            v-model="eventForm.weekly_hours[index].hours"
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
                                            Date overrides
                                        </p>
                                    </header>

                                    <div class="card-content">
                                        <div class="columns is-multiline">
                                            <div class="column is-full has-text-centered">
                                                Add dates when your availability changes from your weekly hours
                                            </div>

                                            <div class="column is-full has-text-centered">
                                                <biz-button
                                                    class="is-info is-fullwidth"
                                                    type="button"
                                                    @click="openDateOverrideModal()"
                                                >
                                                    Add Date
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
                                                                Unavailable
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
                                <biz-button-link
                                    :href="route(baseRouteName+'.index')"
                                    class="is-link is-light"
                                >
                                    Cancel
                                </biz-button-link>
                            </div>
                            <div class="control">
                                <biz-button class="is-link">
                                    Update
                                </biz-button>
                            </div>
                        </div>
                    </div>
                </form>
            </biz-provide-inject-tab>

            <biz-provide-inject-tab
                title="Manager"
                :is-rendered="can.productManager.edit"
            >
                <form
                    v-if="can.productManager.edit"
                    class="box"
                    @submit.prevent="submitManager"
                >
                    <biz-form-assign-user
                        v-model="productManagers"
                        label="Choose Product Manager"
                        :get-users-url="route(productManagerBaseRoute+'.search', product.id)"
                    />

                    <div class="field is-grouped is-grouped-right mt-4">
                        <div class="control">
                            <biz-button class="is-link">
                                Update
                            </biz-button>
                        </div>
                    </div>
                </form>
            </biz-provide-inject-tab>
        </biz-provide-inject-tabs>

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
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFormAssignUser from '@/Biz/Form/AssignUser';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormNumberAddons from '@/Biz/Form/NumberAddons';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import BizGmapMarker from '@/Biz/GmapMarker';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs';
    import BizTag from '@/Biz/Tag';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasTab from '@/Mixins/HasTab';
    import ProductEditModalDateOverride from './ProductEditModalDateOverride';
    import ProductForm from './ProductForm';
    import ScheduleRuleTimes from '@booking/Pages/ScheduleRuleTimes';
    import icon from '@/Libs/icon-class';
    import moment from 'moment';
    import { cloneDeep, forEach, map, sortBy, isEqual, groupBy, intersection, remove, uniq } from 'lodash';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { generateElementId } from '@/Libs/utils';
    import { ref } from 'vue';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizCheckbox,
            BizErrorNotifications,
            BizFormAssignUser,
            BizFormInput,
            BizFormNumberAddons,
            BizFormSelect,
            BizFormTextarea,
            BizGmapMarker,
            BizProvideInjectTab,
            BizProvideInjectTabs,
            BizTag,
            ProductEditModalDateOverride,
            ProductForm,
            ScheduleRuleTimes,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
            MixinHasTab,
            MixinHasModal,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true},
            can: { type: Object, required: true },
            roleOptions: { type: Array, required: true },
            statusOptions: { type: Array, required: true },
            eventDurationOptions: { type: Array, required: true },
            imageMimes: { type: Array, required: true },
            product: { type: Object, required: true },
            event: { type: Object, required: true },
            timezoneOptions: { type: Object, required: true },
            weekdays: { type: Object, required: true },
            weeklyHours: { type: Object, required: true },
            dateOverrides: { type: Array, required: true },
            geoLocation: { type: Object, required: true },
            managers: { type: Array, default: () => [] },
            googleApiKey: { type: String, default: null },
            formatDateIso: { type: String, default: 'YYYY-MM-DD' },
            formatDateUser: { type: String, default: 'D MMM YYYY' },
            productManagerBaseRoute: { type: String, required: true },
            rules: { type: Object, required: true },
        },

        setup(props, { emit }) {
            const form = {
                name: props.product.name,
                status: props.product.status,
                description: props.product.description,
                short_description: props.product.short_description,
                roles: props.product.roles,
                is_check_in_required: props.product.is_check_in_required,
                gallery: {
                    deleted_media: [],
                    files: [],
                },
            };

            const eventForm = {
                location: props.event.location,
                duration: props.event.duration,
                bookable_date_range: props.event.bookable_date_range,
                timezone: props.event.timezone,
                weekly_hours: props.weeklyHours,
                date_overrides: cloneDeep(props.dateOverrides),
            }

            return {
                form: useForm(form),
                eventForm: useForm(eventForm),
                icon,
                eventErrors: ref({}),
            };
        },

        data() {
            return {
                activeTab: 0,
                selectedDateOverrideBatch: null,
                isMapOpen: false,
                productManagers: this.managers,
                unusedDates: [],
            };
        },

        computed: {
            dateOverrideBatches() {
                const self = this;

                const errorBag = 'updateEvent'

                const dateOverrideBatches = [];

                const dateOverrides = this.eventForm.date_overrides.map((dateOverride, index) => {
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
                    .forEach((rawDateOverride, index) => {

                        const dateOverride = cloneDeep(rawDateOverride);

                        if (index == 0) {
                            dateOverride.batch = generateElementId();

                        } else {

                            const latestDateOverrideBatch = dateOverrideBatches[index - 1];

                            const nextDate = moment(latestDateOverrideBatch.started_date)
                                .add(1, 'days')
                                .format(self.formatDateIso);

                            const fnTimeRange = (time) => time.started_time + ' - ' + time.ended_time;

                            let isSimilar = false;

                            if (dateOverride.started_date == nextDate) {
                                if (
                                    !latestDateOverrideBatch.is_available
                                    && !dateOverride.is_available
                                ) {
                                    isSimilar = true;

                                } else {

                                    const latestTimes = latestDateOverrideBatch.times
                                        .map(fnTimeRange)
                                        .sort();

                                    const dateOverrideTimes = dateOverride.times
                                        .map(fnTimeRange)
                                        .sort();

                                    isSimilar = isEqual(latestTimes, dateOverrideTimes);
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
                })

                return errors;
            },
        },

        methods: {
            submit() {
                const self = this;
                const url = route(this.baseRouteName + '.update', {
                    'product': this.product.id
                });

                this
                    .form
                    .transform((data) => ({
                        ...data,
                        _method: 'put',
                    }))
                    .post(url, {
                        forceFormData: true,
                        onStart: self.onStartLoadingOverlay,
                        onSuccess: (page) => {
                            self.form.gallery.files = [];
                            self.$refs.product_form.$refs.file_upload.reset();

                            successAlert(page.props.flash.message);
                        },
                        onError: () => { oopsAlert() },
                        onFinish: self.onEndLoadingOverlay,
                    });
            },

            eventSubmit() {
                const self = this;

                const url = route(this.baseRouteName + '.events.update', {
                    product: this.product.id
                });

                this.eventForm.date_overrides = this.eventForm.date_overrides.filter((dateOverride) => {
                    return !self.unusedDates.includes(dateOverride.started_date);
                });

                this.eventForm.put(url, {
                    errorBag: 'updateEvent',
                    onStart: () => self.onStartLoadingOverlay(),
                    onSuccess: (page) => {
                        self.eventForm.date_overrides = cloneDeep(page.props.dateOverrides);
                        self.eventErrors = {};

                        successAlert(page.props.flash.message);
                    },
                    onError: (errors) => {
                        self.eventErrors = errors;

                        oopsAlert();
                    },
                    onFinish: () => self.onEndLoadingOverlay(),
                });
            },

            addTimeRange(index) {
                const scheduleRuleTime = this.$refs['scheduleRuleTime_' + index][0];
                scheduleRuleTime.addTimeRange();

                if (this.eventForm.weekly_hours[index].hours.length > 0) {
                    this.eventForm.weekly_hours[index].is_available = true;
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
                }

                this.openModal();
            },

            afterApply() {
                const self = this;

                const dateOverrides = this.eventForm.date_overrides;

                self.selectedDateOverrideBatch.dates.forEach((date) => {

                    const startedDate = moment(date);
                    const formattedDate = startedDate.format(self.formatDateIso);

                    const index = dateOverrides.findIndex(function (dateOverride) {
                        return dateOverride.started_date == formattedDate;
                    });

                    if (index === -1) {
                        dateOverrides.push({
                            started_date: formattedDate,
                            times: self.selectedDateOverrideBatch.times,
                            is_available: self.selectedDateOverrideBatch.times.length > 0,
                            display_dates: startedDate.format(self.formatDateUser),
                        });

                    } else {
                        const dateOverride = dateOverrides[index];

                        dateOverride.started_date = formattedDate;
                        dateOverride.times = self.selectedDateOverrideBatch.times;
                        dateOverride.is_available = self.selectedDateOverrideBatch.times.length > 0;
                    }
                });

                const usedDates = intersection(
                    self.selectedDateOverrideBatch.dates.map(
                        (date) => moment(date).format(self.formatDateIso)
                    ),
                    self.unusedDates
                );

                remove(self.unusedDates, (date) => usedDates.includes(date));

                if (self.selectedDateOverrideBatch.unusedDates.length > 0) {
                    self.unusedDates = self.unusedDates.concat(
                        self.selectedDateOverrideBatch.unusedDates
                    );
                }
            },

            displayDates(batch) {
                const dateOverrideBatch = this.dateOverrideBatches[ batch ];

                let displayDates = dateOverrideBatch[ 0 ].display_dates;

                if (dateOverrideBatch.length > 1) {
                    displayDates += ' - ' + dateOverrideBatch[ dateOverrideBatch.length - 1].display_dates;
                }

                return displayDates;
            },

            removeDateOverride(batch) {
                const self = this;

                confirmDelete(
                    'Are you sure want to delete the '+ self.displayDates(batch) + '?',
                ).then(result => {
                    if (result.isConfirmed) {
                        self.dateOverrideBatches[ batch ].forEach((dateOverride) => {
                            let foundedIndex = null;

                            const founded = self.eventForm.date_overrides.find((formDateOverride, index) => {
                                foundedIndex = index;

                                return (formDateOverride.started_date == dateOverride.started_date);
                            });

                            if (founded && founded.id) {
                                self.unusedDates.push(dateOverride.started_date);
                            } else {
                                self.eventForm.date_overrides.splice(foundedIndex, 1);
                            }
                        });
                    }
                })
            },

            toggleMap() {
                this.isMapOpen = !this.isMapOpen;
            },

            submitManager() {
                const self = this;
                const form = useForm({managers: map(this.productManagers, 'id')});

                const url = route(
                    self.productManagerBaseRoute+'.update',
                    self.product.id
                );

                form.post(url, {
                    replace: true,
                    onStart: () => self.onStartLoadingOverlay(),
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: () => self.onEndLoadingOverlay()
                });
            },

            timeRangeRemoved(index) {
                const day = this.eventForm.weekly_hours[index];

                if (day.hours.length == 0) {
                    day.is_available = false;
                }

                this.eventErrors = {};
            },

            checkTimes(index) {
                const day = this.eventForm.weekly_hours[index];

                if (! day.is_available) {
                    day.hours = [];
                } else if (day.hours.length == 0) {
                    this.addTimeRange(index);
                }
            },
        },
    };
</script>
