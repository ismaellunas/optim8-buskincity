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

            <biz-form-input
                v-model="modelCity"
                :label="i18n.city ?? 'City'"
                :maxlength="maxlengthCity"
                :message="error(mergedErrorKey.city, errorBagName, errorBag)"
                :required="isCityRequired"
            />

            <biz-form-select
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
        </div>

        <div
            v-if="isMapEnabled"
            class="column is-6"
        >
            <biz-form-fieldset-geo-location
                v-model:latitude="modelLatitude"
                v-model:longitude="modelLongitude"
                :required="isMapRequired"
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
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: ['i18n'],

        props: {
            address: { type: [String, null], default: null },
            city: { type: [String, null], default: null },
            countryCode: { type: [String, null], default: null },
            latitude: { type: [Number, String, null], default: null },
            longitude: { type: [Number, String, null], default: null },
            errorBag: { type: [Object, null], default: null },
            errorBagName: { type: [String, null], default: null },
            errorKey: { type: Object, default: () => {} },
            isMapEnabled: { type: Boolean, default: true },
            isAddressRequired: { type: Boolean, default: false },
            isCityRequired: { type: Boolean, default: true },
            isCountryRequired: { type: Boolean, default: true },
            isMapRequired: { type: Boolean, default: false },
            googleApiKey: { type: String, default: null },
            initLocation: { type: String, default: null },
            maxlengthAddress: { type: [Number], default: 500 },
            maxlengthCity: { type: [Number], default: 64 },
        },

        emits: [
            'update:latitude',
            'update:longitude',
            'update:address',
            'update:city',
            'update:countryCode',
        ],

        setup(props, { emit }) {
            return {
                countryOptions: ref([]),
                modelAddress: useModelWrapper(props, emit, 'address'),
                modelCity: useModelWrapper(props, emit, 'city'),
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
        },

        beforeMount() {
            this.loadCountryOptions();
        },

        methods: {
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
        },
    };
</script>
