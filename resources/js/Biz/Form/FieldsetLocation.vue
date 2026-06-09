<template>
    <div class="columns">
        <div class="column is-6">
            <biz-form-textarea
                v-model="modelAddress"
                rows="4"
                :label="i18n.address"
                :maxlength="maxlengthAddress"
                :message="error(mergedErrorKey.address, errorBagName, errorBag)"
                :placeholder="i18n.address"
                :required="isAddressRequired"
            />

            <biz-form-city-select
                v-if="showCitySelect"
                v-model="modelCityId"
                :label="i18n.city ?? 'City'"
                :message="error(mergedErrorKey.city, errorBagName, errorBag)"
                :required="isCityRequired"
                :country-code="modelCountryCode"
                :initial-city="initialCity"
                :restricted-cities="restrictedCities"
                :allow-custom-entry="allowCustomCity"
                :placeholder="scopedCityPlaceholder"
                @select="onCitySelect"
            />

            <p
                v-else-if="modelCity"
                class="help mb-3"
            >
                {{ scopedCountryHint }}
            </p>

            <biz-form-select
                v-if="showCountrySelect"
                v-model="modelCountryCode"
                :label="i18n.country ?? 'Country'"
                :message="error(mergedErrorKey.countryCode, errorBagName, errorBag)"
                :required="isCountryRequired"
            >
                <option
                    v-for="countryOption in countryOptions"
                    :key="countryOption.id"
                    :value="countryOption.id"
                >
                    {{ countryOption.value }}
                </option>
            </biz-form-select>

            <p
                v-else-if="modelCity"
                class="help"
            >
                {{ scopedCountryHint }}
            </p>
        </div>

        <div
            v-if="isMapEnabled"
            class="column is-6"
        >
            <biz-form-fieldset-geo-location
                v-model:latitude="modelLatitude"
                v-model:longitude="modelLongitude"
                :google-api-key="googleApiKey"
                :init-location="mapInitLocation"
                :prefer-map-picker="preferMapPicker"
                :error-key="errorKey"
                :required="isMapRequired"
                :error-bag-name="errorBagName"
                @apply="applyMarker"
            />
        </div>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import BizFormFieldsetGeoLocation from '@/Biz/Form/FieldsetGeoLocation.vue';
    import BizFormCitySelect from '@/Biz/Form/CitySelect.vue';
    import BizGmapMarker from '@/Biz/GmapMarker.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { computed, ref } from 'vue';

    export default {
        name: 'FieldsetLocation',

        components: {
            BizFormFieldsetGeoLocation,
            BizFormInput,
            BizFormSelect,
            BizFormTextarea,
            BizFormCitySelect,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: ['i18n'],

        props: {
            address: { type: [String, null], default: null },
            city: { type: [String, null], default: null },
            cityId: { type: [Number, String, null], default: null },
            initialCity: { type: Object, default: null },
            countryCode: { type: [String, null], default: null },
            latitude: { type: [Number, String, null], default: null },
            longitude: { type: [Number, String, null], default: null },
            errorBag: { type: [Object, null], default: null },
            errorBagName: { type: [String, null], default: 'default' },
            errorKey: { type: Object, default: () => {} },
            isMapEnabled: { type: Boolean, default: true },
            isAddressRequired: { type: Boolean, default: false },
            isCityRequired: { type: Boolean, default: true },
            isCountryRequired: { type: Boolean, default: true },
            isMapRequired: { type: Boolean, default: false },
            googleApiKey: { type: String, default: null },
            initLocation: { type: Object, default: null },
            /** When true, coordinates must be set via the map picker (city selection only centers the map). */
            preferMapPicker: { type: Boolean, default: true },
            maxlengthAddress: { type: [Number], default: 500 },
            maxlengthCity: { type: [Number], default: 64 },
            restrictedCities: { type: Array, default: () => [] },
            allowCustomCity: { type: Boolean, default: true }, // Allow custom city entry by default
            /** When false, country is derived from the selected city (scoped city admins). */
            showCountrySelect: { type: Boolean, default: true },
            /** When false, city is set elsewhere (e.g. Location parent picker for scoped admins). */
            showCitySelect: { type: Boolean, default: true },
        },

        emits: [
            'update:latitude',
            'update:longitude',
            'update:address',
            'update:city',
            'update:cityId',
            'update:countryCode',
        ],

        setup(props, { emit }) {
            return {
                countryOptions: ref([]),
                mapCenterLocation: ref(null),
                modelAddress: useModelWrapper(props, emit, 'address'),
                modelCity: useModelWrapper(props, emit, 'city'),
                modelCityId: useModelWrapper(props, emit, 'cityId'),
                modelCountryCode: useModelWrapper(props, emit, 'countryCode'),
                modelLatitude: useModelWrapper(props, emit, 'latitude'),
                modelLongitude: useModelWrapper(props, emit, 'longitude'),
            };
        },

        computed: {
            mergedErrorKey() {
                return {
                    ...{
                        address: 'address',
                        city: 'city',
                        countryCode: 'country_code',
                        latitude: 'latitude',
                        longitude: 'longitude',
                    },
                    ...this.errorKey,
                };
            },

            isScopedCityPicker() {
                return this.restrictedCities.length > 0;
            },

            scopedCityPlaceholder() {
                return this.isScopedCityPicker
                    ? 'Select your city...'
                    : 'Search for a city...';
            },

            scopedCountryHint() {
                if (! this.modelCountryCode) {
                    return '';
                }

                const match = this.restrictedCities.find(
                    (city) => city.id === this.modelCityId || city.name === this.modelCity
                );

                const code = match?.country_code ?? this.modelCountryCode;

                return `Country: ${code} (set from your selected city)`;
            },

            mapInitLocation() {
                if (this.modelLatitude != null && this.modelLongitude != null) {
                    return {
                        latitude: Number.parseFloat(this.modelLatitude),
                        longitude: Number.parseFloat(this.modelLongitude),
                    };
                }

                if (this.mapCenterLocation) {
                    return this.mapCenterLocation;
                }

                return this.initLocation;
            },
        },

        beforeMount() {
            if (this.showCountrySelect) {
                this.loadCountryOptions();
            }

            this.syncMapCenterFromCityId(this.modelCityId);
        },

        watch: {
            modelCityId(cityId) {
                this.syncMapCenterFromCityId(cityId);
            },

            initLocation: {
                handler(location) {
                    if (location?.latitude != null && location?.longitude != null) {
                        this.mapCenterLocation = {
                            latitude: Number.parseFloat(location.latitude),
                            longitude: Number.parseFloat(location.longitude),
                        };
                    }
                },
                immediate: true,
                deep: true,
            },
        },

        methods: {
            syncMapCenterFromCityId(cityId) {
                if (! cityId) {
                    return;
                }

                const city = this.restrictedCities.find(
                    (entry) => Number(entry.id) === Number(cityId)
                );

                if (city?.latitude != null && city?.longitude != null) {
                    this.mapCenterLocation = {
                        latitude: Number.parseFloat(city.latitude),
                        longitude: Number.parseFloat(city.longitude),
                    };
                }
            },

            loadCountryOptions() {
                axios
                    .get(route('admin.api.options.countries'))
                    .then((response) => {
                        this.countryOptions = response.data;
                    });
            },

            applyMarker(marker) {
                this.modelLatitude = marker.latitude;
                this.modelLongitude = marker.longitude;
            },

            onCitySelect(city) {
                this.mapCenterLocation = (
                    city.latitude != null && city.longitude != null
                ) ? {
                    latitude: Number.parseFloat(city.latitude),
                    longitude: Number.parseFloat(city.longitude),
                } : null;

                if (city.isCustom) {
                    this.modelCity = city.name;
                    this.modelCityId = null;
                    if (city.country_code) this.modelCountryCode = city.country_code;

                    if (this.preferMapPicker) {
                        this.modelLatitude = null;
                        this.modelLongitude = null;
                    }

                    return;
                }

                this.modelCity = city.name;
                this.modelCityId = city.id;
                this.modelCountryCode = city.country_code;

                if (this.preferMapPicker) {
                    this.modelLatitude = null;
                    this.modelLongitude = null;

                    return;
                }

                if (city.latitude) this.modelLatitude = city.latitude;
                if (city.longitude) this.modelLongitude = city.longitude;
            }
        },
    };
</script>
