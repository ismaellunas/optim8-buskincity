<template>
    <div class="fieldset-geo-location">
        <div
            v-if="preferMapPicker && computedGoogleApiKey"
            class="map-picker-primary"
        >
            <biz-label>
                {{ i18n.map_location ?? i18n.map ?? 'Location on map' }}
                <span
                    v-if="required"
                    class="has-text-danger"
                >*</span>
            </biz-label>

            <p class="help mb-2">
                {{ mapPickerHelpText }}
            </p>

            <biz-button
                type="button"
                class="is-info"
                @click="openMapModal"
            >
                <span class="icon">
                    <i :class="icon.locationMark" />
                </span>
                <span>{{ i18n.pick_on_map ?? 'Pick location on map' }}</span>
            </biz-button>

            <p
                v-if="hasCoordinates"
                class="help mt-2"
            >
                {{ coordinateSummary }}
            </p>

            <p
                v-else
                class="help mt-2 has-text-grey"
            >
                {{ i18n.no_location_selected ?? 'No location selected yet' }}
            </p>

            <p
                v-if="error(mergedErrorKey.latitude, errorBagName, errorBag)"
                class="help is-danger"
            >
                {{ error(mergedErrorKey.latitude, errorBagName, errorBag) }}
            </p>

            <p
                v-if="error(mergedErrorKey.longitude, errorBagName, errorBag)"
                class="help is-danger"
            >
                {{ error(mergedErrorKey.longitude, errorBagName, errorBag) }}
            </p>
        </div>

        <div
            v-else-if="preferMapPicker && !computedGoogleApiKey"
            class="notification is-warning is-light mb-3"
        >
            {{ i18n.map_api_key_missing ?? 'Google Maps is not configured. Contact an administrator to enable the map picker, or enter coordinates manually below.' }}
        </div>

        <div
            v-if="!preferMapPicker || !computedGoogleApiKey"
            class="columns is-multiline"
        >
            <div class="column is-9">
                <biz-form-input
                    v-model="modelLatitude"
                    type="number"
                    step="any"
                    :label="i18n.latitude"
                    :message="error(mergedErrorKey.latitude, errorBagName, errorBag)"
                    :required="required"
                />
            </div>

            <div
                v-if="!!computedGoogleApiKey && !preferMapPicker"
                class="column is-3"
            >
                <div class="field">
                    <biz-label>
                        {{ i18n.map ?? 'Map' }}
                    </biz-label>

                    <span class="control">
                        <biz-button-icon
                            type="button"
                            class="is-primary"
                            :icon="icon.locationMark"
                            @click="openMapModal"
                        />
                    </span>
                </div>
            </div>

            <div class="column is-9">
                <biz-form-input
                    v-model="modelLongitude"
                    type="number"
                    step="any"
                    :label="i18n.longitude"
                    :message="error(mergedErrorKey.longitude, errorBagName, errorBag)"
                    :required="required"
                />
            </div>
        </div>

        <biz-modal-card
            v-if="isModalOpen && computedGoogleApiKey"
            :header-title="i18n.map ?? 'Map'"
            @close="closeModal()"
        >
            <div class="columns">
                <div class="column is-full">
                    <div class="card">
                        <div class="card-content p-2">
                            <biz-gmap-marker
                                v-model="mapMarker"
                                :api-key="computedGoogleApiKey"
                                :init-position="computedInitLocation"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div
                    class="columns mx-0"
                    style="width: 100%"
                >
                    <div class="column is-full">
                        <div class="buttons is-right">
                            <biz-button @click.prevent="closeModal()">
                                {{ textCancel }}
                            </biz-button>

                            <biz-button
                                class="is-info ml-1"
                                type="button"
                                @click.prevent="apply"
                            >
                                {{ textApply }}
                            </biz-button>
                        </div>
                    </div>
                </div>
            </template>
        </biz-modal-card>
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizGmapMarker from '@/Biz/GmapMarker.vue';
    import BizLabel from '@/Biz/Label.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import { computed, inject, ref } from 'vue';
    import { locationMark } from '@/Libs/icon-class';
    import { usePage } from '@inertiajs/vue3';

    export default {
        name: 'FieldsetGeoLocation',

        components: {
            BizButton,
            BizButtonIcon,
            BizFormInput,
            BizGmapMarker,
            BizLabel,
            BizModalCard,
        },

        mixins: [
            MixinHasModal,
            MixinHasPageErrors,
        ],

        inject: ['i18n'],

        props: {
            latitude: { type: [Number, String, null], default: null },
            longitude: { type: [Number, String, null], default: null },
            googleApiKey: { type: String, default: null },
            initLocation: { type: Object, default: null },
            preferMapPicker: { type: Boolean, default: true },
            required: { type: Boolean, default: false },
            errorBag: { type: [Object, null], default: null },
            errorBagName: { type: [String, null], default: 'default' },
            errorKey: { type: Object, default: () => {} },
            textApply: { type: String, default: 'Apply'},
            textCancel: { type: String, default: 'Cancel'},
        },

        emits: [
            'apply',
            'update:latitude',
            'update:longitude',
        ],

        setup(props, { emit }) {
            const i18n = inject('i18n', {});

            const computedGoogleApiKey = computed(() => {
                return props.googleApiKey
                    ?? usePage().props.googleApiKey
                    ?? null;
            });

            const modelLatitude = computed({
                get: () => props.latitude,
                set: (value) => emit(
                    'update:latitude',
                    value ? Number.parseFloat(value) : null
                )
            });

            const modelLongitude = computed({
                get: () => props.longitude,
                set: (value) => emit(
                    'update:longitude',
                    value ? Number.parseFloat(value) : null
                )
            });

            const mergedErrorKey = computed(() => {
                return {
                    ...{
                        latitude: 'latitude',
                        longitude: 'longitude',
                    },
                    ...props.errorKey,
                };
            });

            const computedInitLocation = computed(() => {
                return props.initLocation
                    ?? usePage().props.geoLocation
                    ?? { latitude: null, longitude: null };
            });

            const hasCoordinates = computed(() => {
                return props.latitude != null
                    && props.latitude !== ''
                    && props.longitude != null
                    && props.longitude !== '';
            });

            const coordinateSummary = computed(() => {
                const lat = Number.parseFloat(props.latitude);
                const lng = Number.parseFloat(props.longitude);

                return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            });

            const mapPickerHelpText = computed(() => {
                return i18n?.map_picker_help
                    ?? 'Use the map to pin the exact location. Coordinates are set from your map selection.';
            });

            return {
                computedGoogleApiKey,
                computedInitLocation,
                coordinateSummary,
                hasCoordinates,
                icon: { locationMark },
                mapPickerHelpText,
                mergedErrorKey,
                mapMarker: ref({
                    latitude: null,
                    longitude: null,
                }),
                modelLatitude,
                modelLongitude,
            };
        },

        methods: {
            openMapModal() {
                if (this.latitude != null && this.longitude != null) {
                    this.mapMarker.latitude = Number.parseFloat(this.latitude);
                    this.mapMarker.longitude = Number.parseFloat(this.longitude);
                } else if (
                    this.computedInitLocation?.latitude != null
                    && this.computedInitLocation?.longitude != null
                ) {
                    this.mapMarker.latitude = Number.parseFloat(this.computedInitLocation.latitude);
                    this.mapMarker.longitude = Number.parseFloat(this.computedInitLocation.longitude);
                } else {
                    this.mapMarker.latitude = null;
                    this.mapMarker.longitude = null;
                }

                this.openModal();
            },

            apply() {
                this.$emit('update:latitude', this.mapMarker.latitude);
                this.$emit('update:longitude', this.mapMarker.longitude);
                this.$emit('apply', this.mapMarker);

                this.closeModal();
            },
        }
    };
</script>
