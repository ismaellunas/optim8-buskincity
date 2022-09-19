<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

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

                        <biz-form-select
                            v-model="eventForm.duration"
                            label="Duration"
                            :message="error('duration', 'eventForm')"
                            has-addons
                            required
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

                        <biz-form-number-addons
                            v-model="eventForm.bookable_date_range"
                            label="Bookable date range (Calendar days into the future)"
                            :is-expanded="false"
                            :message="error('bookable_date_range', 'eventForm')"
                            required
                        >
                            <template #afterInput>
                                <p class="control">
                                    <a class="button is-static">
                                        day(s)
                                    </a>
                                </p>
                            </template>
                        </biz-form-number-addons>

                        <biz-form-textarea
                            v-model="eventForm.location.address"
                            label="Address"
                            placeholder="Address"
                            rows="2"
                            maxlength="500"
                            :message="error('location.address', 'eventForm')"
                        />

                        <div class="columns is-multiline">
                            <div class="column is-5">
                                <biz-form-input
                                    v-model="eventForm.location.latitude"
                                    label="Latitude"
                                    :message="error('location.latitude', 'eventForm')"
                                />
                            </div>
                            <div class="column is-5">
                                <biz-form-input
                                    v-model="eventForm.location.longitude"
                                    label="Longitude"
                                    :message="error('location.longitude', 'eventForm')"
                                />
                            </div>
                            <div class="column is-2">
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

                            <div
                                v-if="isMapOpen"
                                class="column is-8"
                            >
                                <biz-gmap-marker
                                    v-model="eventForm.location"
                                    :init-position="geoLocation"
                                />
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
                                                        >
                                                            &nbsp;{{ weekday }}
                                                        </biz-checkbox>
                                                    </div>
                                                    <div class="column">
                                                        <table class="table is-fullwidth">
                                                            <tr
                                                                v-for="(hour, hourIdx) in eventForm.weekly_hours[index].hours"
                                                                :key="hour.uid"
                                                            >
                                                                <td>
                                                                    <div class="event-time-range">
                                                                        <biz-date-time
                                                                            v-model="eventForm.weekly_hours[index].hours[hourIdx].timeRange"
                                                                            type="time"
                                                                            :options="timeRangeOptions"
                                                                            range
                                                                            @input="onTimeInput(eventForm.weekly_hours[index].hours[hourIdx], $event)"
                                                                        />
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="buttons is-pulled-right">
                                                                        <biz-button-icon
                                                                            type="button"
                                                                            class="is-danger"
                                                                            :icon="icon.remove"
                                                                            @click="removeTimeRange(eventForm.weekly_hours[index].hours, hourIdx)"
                                                                        />
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="column is-2">
                                                        <biz-button-icon
                                                            type="button"
                                                            :icon="icon.add"
                                                            @click="addTimeRange(eventForm.weekly_hours[index].hours)"
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
                                                    v-for="dateOverride, dateOverrideIdx in eventForm.date_overrides"
                                                    :key="dateOverride.uid"
                                                    class="columns"
                                                >
                                                    <div class="column is-5">
                                                        {{ dateOverride.displayDates }}
                                                    </div>

                                                    <div class="column is-4 has-text-centered">
                                                        <div
                                                            v-if="dateOverride.is_available"
                                                            class="columns is-multiline"
                                                        >
                                                            <div
                                                                v-for="time in dateOverride.times"
                                                                :key="time.uid"
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
                                                                    @click="removeDateOverride(dateOverrideIdx)"
                                                                />
                                                            </div>
                                                            <div class="control">
                                                                <biz-button-icon
                                                                    type="button"
                                                                    :icon="icon.edit"
                                                                    @click="openDateOverrideModal(dateOverride)"
                                                                />
                                                            </div>
                                                        </div>
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
        </biz-provide-inject-tabs>

        <product-edit-modal-date-override
            v-if="isModalOpen"
            id="product-event-date-override-modal"
            v-model="selectedDateOverride"
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
    import BizDateTime from '@/Biz/DateTime';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
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
    import icon from '@/Libs/icon-class';
    import moment from 'moment';
    import { cloneDeep, padStart } from 'lodash';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { generateElementId } from '@/Libs/utils';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizCheckbox,
            BizDateTime,
            BizErrorNotifications,
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
        },

        setup(props, { emit }) {
            const form = {
                name: props.product.name,
                status: props.product.status,
                description: props.product.description,
                short_description: props.product.short_description,
                roles: props.product.roles ?? null,
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
                date_overrides: props.dateOverrides,
            }

            for (const dayNumber in props.weeklyHours) {
                let dayHours = eventForm.weekly_hours[dayNumber].hours;

                props.weeklyHours[dayNumber]['hours'].forEach((hour, index) => {
                    let startTimeText = hour.started_time.split(':');
                    let endTimeText = hour.ended_time.split(':');

                    dayHours[index].timeRange = [
                        new Date(0,0,0,startTimeText[0],startTimeText[1]),
                        new Date(0,0,0,endTimeText[0],endTimeText[1]),
                    ];

                    dayHours[index].uid = hour.id;
                });
            }

            eventForm.date_overrides.forEach((dateOverride, index) => {
                dateOverride.uid = generateElementId();

                dateOverride.times.forEach((time) => {
                    time.uid = generateElementId();
                });
            });

            return {
                form: useForm(form),
                eventForm: useForm(eventForm),
                icon,
            };
        },

        data() {
            return {
                activeTab: 0,
                defaultTimeRange: [
                    new Date(0,0,0,9,0),
                    new Date(0,0,0,17,0),
                ],
                timeRangeOptions: {
                    color: 'link',
                    startTime: new Date(0,0,0,9,0),
                    endTime: new Date(0,0,0,17,0),
                },
                selectedDateOverride: null,
                isMapOpen: false,
            };
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
                const url = route(this.baseRouteName + '.events.update', {
                    'product': this.product.id
                });

                this.eventForm.put(url, {
                    onStart: self.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: self.onEndLoadingOverlay,
                });
            },

            scheduleSubmit() {
                const url = route(this.baseRouteName + '.schedule.update', {
                    'product': this.product.id
                });
            },

            addTimeRange(hours) {
                hours.push({
                    started_time: "09:00",
                    ended_time: "17:00",
                    timeRange: cloneDeep(this.defaultTimeRange),
                    uid: generateElementId(),
                });
            },

            removeTimeRange(hours, index) {
                hours.splice(index, 1);
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

            openDateOverrideModal(dateOverride) {
                if (!dateOverride) {
                    this.selectedDateOverride = {
                        started_date: moment().format('YYYY-MM-DD'),
                        ended_date: moment().format('YYYY-MM-DD'),
                        is_available: false,
                        displayDates: null,
                        uid: generateElementId(),
                        times: [],
                    };
                } else {
                    this.selectedDateOverride = dateOverride;
                }

                this.openModal();
            },

            afterApply() {
                const hasUid = (dateOverride) => dateOverride.uid == this.selectedDateOverride.uid;

                if (! this.eventForm.date_overrides.some(hasUid)) {
                    this.eventForm.date_overrides.push(
                        cloneDeep(this.selectedDateOverride)
                    );
                }
            },

            removeDateOverride(index) {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.eventForm.date_overrides.splice(index, 1);
                    }
                });
            },

            toggleMap() {
                this.isMapOpen = !this.isMapOpen;
            },
        },
    };
</script>
