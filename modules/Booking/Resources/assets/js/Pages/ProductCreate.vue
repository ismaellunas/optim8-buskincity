<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="mb-6">
            <form
                method="post"
                @submit.prevent="submit"
            >
                <div class="box">
                    <product-form
                        v-model="form"
                        :event-duration-options="eventDurationOptions"
                        :image-mimes="imageMimes"
                        :instructions="instructions"
                        :location-fieldset-error-keys="locationFieldsetErrorKeys"
                        :max-pitch-date-span-days="maxPitchDateSpanDays"
                        :role-options="roleOptions"
                        :rules="rules"
                        :status-options="statusOptions"
                        :space-options="spaceOptions"
                        :weekdays="weekdays"
                        :weekly-hours="form.weekly_hours"
                    >
                        <template #schedule>
                            <div class="card">
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
                                                        :errors="errors"
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
                        </template>
                    </product-form>

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
                                {{ i18n.create }}
                            </biz-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import ProductForm from './ProductForm.vue';
    import ScheduleRuleTimes from '@booking/Pages/ScheduleRuleTimes.vue';
    import icon from '@/Libs/icon-class';
    import { cloneDeep } from 'lodash';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizCheckbox,
            BizErrorNotifications,
            ProductForm,
            ScheduleRuleTimes,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
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
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            dimensions: { type: Object, default: () => {} },
            imageMimes: { type: Array, required: true },
            instructions: { type: Object, default: () => {} },
            roleOptions: { type: Array, required: true },
            rules: { type: Object, required: true },
            statusOptions: { type: Array, required: true },
            spaceOptions: { type: Array, default: () => [] },
            eventDurationOptions: { type: Array, required: true },
            weekdays: { type: Object, required: true },
            weeklyHours: { type: Object, required: true },
            defaultCountryCode: { type: String, required: true },
            defaultTimezone: { type: String, required: true },
            isSpecialEventPitch: { type: Boolean, default: false },
            maxPitchDateSpanDays: { type: Number, default: null },
            i18n: { type: Object, default: () => ({
                cancel: 'Cancel',
                create: 'Create',
            }) },
        },

        setup(props) {
            const locationFieldsetErrorKeys = {
                address: 'location.address',
                city: 'location.city',
                countryCode: 'location.country_code',
                latitude: 'location.latitude',
                longitude: 'location.longitude',
            };

            const form = {
                name: null,
                status: 'draft',
                description: null,
                short_description: null,
                roles: null,
                is_check_in_required: false,
                gallery: [],
                space_id: null,
                location: {
                    address: null,
                    city: null,
                    city_id: null,
                    country_code: props.defaultCountryCode,
                    latitude: null,
                    longitude: null,
                },
                duration: '60',
                bookable_date_range: props.isSpecialEventPitch ? 14 : 60,
                pitch_started_at: null,
                pitch_ended_at: null,
                timezone: props.defaultTimezone,
                weekly_hours: cloneDeep(props.weeklyHours),
                date_overrides: [],
            };

            return {
                form: useForm(form),
                icon,
                locationFieldsetErrorKeys,
            };
        },

        methods: {
            submit() {
                const self = this;
                const url = route(this.baseRouteName + '.store');

                this.form.post(url, {
                    onStart: self.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert(); },
                    onFinish: self.onEndLoadingOverlay,
                });
            },

            addTimeRange(index) {
                const scheduleRuleTime = this.$refs['scheduleRuleTime_' + index][0];
                scheduleRuleTime.addTimeRange();

                if (this.form.weekly_hours[index].hours.length > 0) {
                    this.form.weekly_hours[index].is_available = true;
                }
            },

            timeRangeRemoved(index) {
                const day = this.form.weekly_hours[index];

                if (day.hours.length === 0) {
                    day.is_available = false;
                }
            },

            checkTimes(index) {
                const day = this.form.weekly_hours[index];

                if (! day.is_available) {
                    day.hours = [];
                } else if (day.hours.length === 0) {
                    this.addTimeRange(index);
                }
            },
        },
    };
</script>
