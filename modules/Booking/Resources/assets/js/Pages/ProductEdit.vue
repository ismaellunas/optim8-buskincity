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
                            :gallery="product.gallery"
                            :image-mimes="imageMimes"
                            :instructions="instructions"
                            :role-options="roleOptions"
                            :rules="rules"
                            :status-options="statusOptions"
                        />

                        <hr>

                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button-link
                                    :href="route(baseRouteName+'.index')"
                                    class="is-link is-light"
                                >
                                    {{ i18n.cancel }}
                                </biz-button-link>
                            </div>
                            <div class="control">
                                <biz-button class="is-link">
                                    {{ i18n.update }}
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
                            {{ i18n.details }}
                        </h5>

                        <hr class="mt-0">

                        <div class="columns">
                            <div class="column is-6">
                                <biz-form-select
                                    v-model="eventForm.duration"
                                    :label="i18n.duration"
                                    :message="error('duration', eventErrorBag)"
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
                                    :label="i18n.bookable_date_range"
                                    max="365"
                                    min="0"
                                    required
                                    :message="error('bookable_date_range', eventErrorBag)"
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

                        <biz-form-fieldset-location
                            v-model:address="eventForm.location.address"
                            v-model:city="eventForm.location.city"
                            v-model:country-code="eventForm.location.country_code"
                            v-model:latitude="eventForm.location.latitude"
                            v-model:longitude="eventForm.location.longitude"
                            :error-bag-name="eventErrorBag"
                            :error-key="locationFieldsetErrorKeys"
                        />
                    </div>

                    <div class="box">
                        <h5 class="title is-5 mb-2">
                            {{ i18n.schedule }}
                        </h5>

                        <hr class="mt-0">

                        <biz-form-timezone
                            v-model="eventForm.timezone"
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
                                <biz-button-link
                                    :href="route(baseRouteName+'.index')"
                                    class="is-link is-light"
                                >
                                    {{ i18n.cancel }}
                                </biz-button-link>
                            </div>
                            <div class="control">
                                <biz-button class="is-link">
                                    {{ i18n.update }}
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
                        :label="i18n.choose_product_manager"
                        :get-users-url="route(productManagerBaseRoute+'.search', product.id)"
                    />

                    <div class="field is-grouped is-grouped-right mt-4">
                        <div class="control">
                            <biz-button class="is-link">
                                {{ i18n.update }}
                            </biz-button>
                        </div>
                    </div>
                </form>
            </biz-provide-inject-tab>

            <biz-provide-inject-tab
                id="product-space"
                title="Space"
                :is-rendered="can.space.manageProductSpace"
            >
                <form
                    class="box"
                    @submit.prevent="submitSpace()"
                >
                    <product-space-form
                        v-model="spaceForm.space_id"
                        :space-options="spaceOptions"
                    />

                    <div class="field is-grouped is-grouped-right mt-4">
                        <div class="control">
                            <biz-button class="is-link">
                                {{ i18n.update }}
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
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasTab from '@/Mixins/HasTab';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormAssignUser from '@/Biz/Form/AssignUser.vue';
    import BizFormFieldsetLocation from '@/Biz/Form/FieldsetLocation.vue';
    import BizFormNumberAddons from '@/Biz/Form/NumberAddons.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormTimezone from '@/Biz/Form/Timezone.vue';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab.vue';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs.vue';
    import BizTag from '@/Biz/Tag.vue';
    import BizTooltip from '@/Biz/Tooltip.vue';
    import ProductSpaceForm from './ProductSpaceForm.vue';
    import icon from '@/Libs/icon-class';
    import moment from 'moment';
    import ProductEditModalDateOverride from './ProductEditModalDateOverride.vue';
    import ProductForm from './ProductForm.vue';
    import ScheduleRuleTimes from '@booking/Pages/ScheduleRuleTimes.vue';
    import { cloneDeep, forEach, map, sortBy, isEqual, groupBy, intersection, remove, uniq } from 'lodash';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { generateElementId } from '@/Libs/utils';
    import { ref, computed } from 'vue';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizCheckbox,
            BizErrorNotifications,
            BizFormAssignUser,
            BizFormFieldsetLocation,
            BizFormNumberAddons,
            BizFormSelect,
            BizFormTimezone,
            BizProvideInjectTab,
            BizProvideInjectTabs,
            BizTag,
            BizTooltip,
            ProductEditModalDateOverride,
            ProductForm,
            ScheduleRuleTimes,
            ProductSpaceForm,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
            MixinHasTab,
            MixinHasModal,
        ],

        provide() {
            return {
                can: this.can,
                i18n: this.i18n,
                dimensions: this.dimensions,
            };
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true},
            can: { type: Object, required: true },
            dimensions: { type: Object, default: () => {} },
            roleOptions: { type: Array, required: true },
            defaultCountryCode: { type: String, required: true },
            statusOptions: { type: Array, required: true },
            eventDurationOptions: { type: Array, required: true },
            imageMimes: { type: Array, required: true },
            product: { type: Object, required: true },
            event: { type: Object, required: true },
            weekdays: { type: Object, required: true },
            weeklyHours: { type: Object, required: true },
            dateOverrides: { type: Array, required: true },
            managers: { type: Array, default: () => [] },
            formatDateIso: { type: String, default: 'YYYY-MM-DD' },
            formatDateUser: { type: String, default: 'D MMM YYYY' },
            productManagerBaseRoute: { type: String, required: true },
            rules: { type: Object, required: true },
            instructions: {type: Object, default: () => {}},
            spaceOptions: { type: Array, default: () => [] },
            space: { type: Object, required: true },
            i18n: { type: Object, default: () => {} },
        },

        setup(props) {
            const product = computed(() => props.product);
            const event = computed(() => props.event);
            const space = computed(() => props.space);

            const form = {
                name: product.value.name,
                status: product.value.status,
                description: product.value.description,
                short_description: product.value.short_description,
                roles: product.value.roles,
                is_check_in_required: product.value.is_check_in_required,
                gallery: product.value.gallery.map(media => media.id),
            };

            const eventForm = {
                location: event.value.location,
                duration: event.value.duration,
                bookable_date_range: event.value.bookable_date_range,
                timezone: event.value.timezone,
                weekly_hours: computed(() => props.weeklyHours).value,
                date_overrides: cloneDeep(computed(() => props.dateOverrides).value),
            };

            const spaceForm = {
                space_id: space.value.id,
            };

            const locationFieldsetErrorKeys = {
                address: 'location.address',
                city: 'location.city',
                countryCode: 'location.country_code',
                latitude: 'location.latitude',
                longitude: 'location.longitude'
            };

            return {
                form: useForm(form),
                eventErrorBag: 'updateEvent',
                eventErrors: ref({}),
                eventForm: useForm(eventForm),
                spaceForm: useForm(spaceForm),
                icon,
                locationFieldsetErrorKeys,
            };
        },

        data() {
            return {
                activeTab: 0,
                selectedDateOverrideBatch: null,
                productManagers: this.managers,
                unusedDates: [],
            };
        },

        computed: {
            dateOverrideBatches() {
                const self = this;

                const errorBag = this.eventErrorBag;

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
                    errorBag: self.eventErrorBag,
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

            submitSpace() {
                const self = this;

                const url = route(
                    self.baseRouteName+'.spaces.update',
                    self.product.id
                );

                self.spaceForm.put(url, {
                    replace: true,
                    onStart: () => self.onStartLoadingOverlay(),
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: () => self.onEndLoadingOverlay()
                });
            }
        },
    };
</script>
