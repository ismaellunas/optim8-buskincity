<template>
    <div class="columns is-multiline">
        <div class="column is-9">
            <biz-form-input
                v-model="modelLatitude"
                type="number"
                :label="i18n.latitude"
                :message="error(mergedErrorKey.latitude, null, errorBag)"
                :required="required"
            />
        </div>

        <div
            v-if="!!computedGoogleApiKey"
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
                :label="i18n.longitude"
                :message="error(mergedErrorKey.longitude, null, errorBag)"
                :required="required"
            />
        </div>
    </div>

    <biz-modal-card
        v-if="isModalOpen && computedGoogleApiKey"
        header-title="Map"
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
    import { useModelWrapper } from '@/Libs/utils';
    import { computed, ref } from 'vue';
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
            required: { type: Boolean, default: false },
            errorBag: { type: Object, default: () => {} },
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
            })

            const modelLongitude = computed({
                get: () => props.longitude,
                set: (value) => emit(
                    'update:longitude',
                    value ? Number.parseFloat(value) : null
                )
            })

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
                    ?? { latitude: null, longitude: null }
            });

            return {
                computedGoogleApiKey,
                computedInitLocation,
                icon: { locationMark },
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
                this.mapMarker.latitude = this.latitude;
                this.mapMarker.longitude = this.longitude;

                this.openModal();
            },

            apply() {
                this.$emit('apply', this.mapMarker);

                this.closeModal();
            },
        }
    };
</script>
