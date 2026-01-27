<template>
    <div>
        <h5 class="title is-5 mb-2">
            {{ i18n.details }}
        </h5>

        <biz-form-input
            v-model="form.name"
            :label="i18n.name"
            required
            :message="error('name')"
        />

        <biz-form-input
            v-model="form.short_description"
            :label="i18n.short_description"
            required
            :message="error('short_description')"
        />

        <biz-form-textarea
            v-model="form.description"
            :label="i18n.description"
            required
            rows="3"
            :message="error('description')"
        />

        <biz-form-select
            v-model="form.status"
            :label="i18n.status"
            :message="error('status')"
        >
            <option
                v-for="statusOption in statusOptions"
                :key="statusOption.id"
                :value="statusOption.id"
            >
                {{ statusOption.value }}
            </option>
        </biz-form-select>

        <biz-form-checkbox-toggle
            v-model="form.is_check_in_required"
            :text="i18n.check_in_required"
            :value="form.is_check_in_required"
        />

        <h5 class="title is-5 mt-5 mb-3">
            {{ i18n.visibility }}
        </h5>

        <biz-form-select
            v-model="form.roles"
            :label="i18n.roles"
            :message="error('roles')"
        >
            <option
                v-for="(roleOption, index) in roleOptions"
                :key="index"
                :value="roleOption.id"
            >
                {{ roleOption.value }}
            </option>
        </biz-form-select>

        <!-- Location Section -->
        <h5 class="title is-5 mt-5 mb-3">
            {{ i18n.location }}
        </h5>

        <biz-form-fieldset-location
            v-if="form.location"
            v-model:address="form.location.address"
            v-model:city="form.location.city"
            v-model:city-id="form.location.city_id"
            v-model:country-code="form.location.country_code"
            v-model:latitude="form.location.latitude"
            v-model:longitude="form.location.longitude"
            :error-key="locationFieldsetErrorKeys"
        />

        <!-- Booking Settings Section -->
        <h5 class="title is-5 mt-5 mb-3">
            {{ i18n.booking_settings }}
        </h5>

        <div class="columns">
            <div class="column is-6">
                <biz-form-select
                    v-if="form.duration !== undefined"
                    v-model="form.duration"
                    :label="i18n.duration"
                    :message="error('duration')"
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
                    v-if="form.bookable_date_range !== undefined"
                    v-model="form.bookable_date_range"
                    :label="i18n.bookable_date_range"
                    max="365"
                    min="0"
                    required
                    :message="error('bookable_date_range')"
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
                <biz-form-date-time
                    v-if="pitchDateRange !== undefined"
                    v-model="pitchDateRange"
                    :label="i18n.pitch_date_range"
                    range
                    :utc="'preserve'"
                    required
                    :message="error(['pitch_started_at', 'pitch_ended_at'])"
                    :enable-time-picker="false"
                    :tooltip-message="i18n.tips?.pitch_date_range"
                />
            </div>
            <div class="column is-6">
                <biz-form-timezone
                    v-if="form.pitch_timezone !== undefined"
                    v-model="form.pitch_timezone"
                    :label="i18n.pitch_timezone"
                    :message="error('pitch_timezone')"
                    required
                />
            </div>
        </div>

        <!-- Schedule Section -->
        <h5 class="title is-5 mt-5 mb-3">
            {{ i18n.schedule }}
            <biz-tooltip
                v-if="i18n.tips?.schedule"
                class="ml-1"
                :message="i18n.tips.schedule"
            />
        </h5>

        <biz-form-timezone
            v-if="form.timezone !== undefined"
            v-model="form.timezone"
            :label="i18n.timezone"
            :message="error('timezone')"
            :tooltip-message="i18n.tips?.timezone"
            required
        />

        <div v-if="weekdays && weeklyHours" class="mt-4">
            <p class="has-text-weight-semibold mb-2">{{ i18n.weekly_hours }}</p>
            <p class="help mb-3">{{ i18n.tips?.weekly_hours }}</p>
            
            <slot name="schedule" />
        </div>

        <h5 class="title is-5 mt-5 mb-3">
            {{ i18n.gallery }}
        </h5>

        <biz-form-multiple-media-library
            v-model="form.gallery"
            :label="i18n.upload"
            :is-download-enabled="can?.media?.read ?? false"
            :is-upload-enabled="can?.media?.add ?? false"
            :is-edit-enabled="can?.media?.edit ?? false"
            :is-browse-enabled="can?.media?.browse ?? false"
            :mediums="gallery"
            :dimension="dimensions.gallery"
            :allow-multiple="true"
            :max-files="rules.maxProductFileNumber"
            :instructions="instructions.mediaLibrary"
            :message="error('gallery')"
        />
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormCheckboxToggle from '@/Biz/Form/CheckboxToggle.vue';
    import BizFormDateTime from '@/Biz/Form/DateTime.vue';
    import BizFormFieldsetLocation from '@/Biz/Form/FieldsetLocation.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormMultipleMediaLibrary from '@/Biz/Form/MultipleMediaLibrary.vue';
    import BizFormNumberAddons from '@/Biz/Form/NumberAddons.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import BizFormTimezone from '@/Biz/Form/Timezone.vue';
    import BizTooltip from '@/Biz/Tooltip.vue';
    import ProductSpaceForm from './ProductSpaceForm.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { computed } from 'vue';

    export default {
        components: {
            BizFormCheckboxToggle,
            BizFormDateTime,
            BizFormFieldsetLocation,
            BizFormInput,
            BizFormMultipleMediaLibrary,
            BizFormNumberAddons,
            BizFormSelect,
            BizFormTextarea,
            BizFormTimezone,
            BizTooltip,
            ProductSpaceForm,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            can: {},
            i18n: { default: () => ({
                details: 'Details',
                name: 'Name',
                short_description: 'Short description',
                description: 'Description',
                status: 'Status',
                check_in_required: 'Is a check-in required?',
                visibility: 'Visibility',
                roles: 'Roles',
                select: 'Select',
                location: 'Location',
                booking_settings: 'Booking Settings',
                duration: 'Duration',
                bookable_date_range: 'Bookable Date Range',
                pitch_date_range: 'Pitch Date Range',
                pitch_timezone: 'Pitch Timezone',
                schedule: 'Schedule',
                timezone: 'Timezone',
                weekly_hours: 'Weekly Hours',
                space: 'Space',
                gallery: 'Gallery',
                upload: 'Upload',
                tips: {},
            }) },
            dimensions: {},
        },

        props: {
            modelValue: { type: Object, required: true },
            statusOptions: { type: Array, required: true },
            roleOptions: { type: Array, required: true },
            gallery: { type: Array, default: () => [] },
            rules: { type: Object, required: true },
            imageMimes: { type: Array, required: true },
            instructions: { type: Object, default: () => {} },
            spaceOptions: { type: Array, default: () => [] },
            eventDurationOptions: { type: Array, default: () => [] },
            locationFieldsetErrorKeys: { type: Object, default: () => ({
                address: 'location.address',
                city: 'location.city',
                countryCode: 'location.country_code',
                latitude: 'location.latitude',
                longitude: 'location.longitude'
            }) },
            weekdays: { type: Object, default: () => null },
            weeklyHours: { type: Object, default: () => null },
        },

        setup(props, { emit }) {
            const form = useModelWrapper(props, emit);

            const pitchDateRange = computed({
                get() {
                    if (form.value.pitch_started_at) {
                        return [
                            form.value.pitch_started_at,
                            form.value.pitch_ended_at,
                        ];
                    }
                    return [];
                },
                set(newValue) {
                    if (newValue == null) {
                        form.value.pitch_started_at = form.value.pitch_ended_at = null;
                    } else {
                        form.value.pitch_started_at = newValue[0] ?? null;
                        form.value.pitch_ended_at = newValue[1] ?? null;
                    }
                }
            });

            return {
                form,
                pitchDateRange,
            };
        },
    };
</script>
