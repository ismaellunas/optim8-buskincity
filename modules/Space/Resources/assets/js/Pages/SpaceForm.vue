<template>
    <div>
        <biz-form-input
            v-model="space.name"
            :label="i18n.name"
            :placeholder="isCityAdmin ? 'e.g Pitch name' : 'e.g My Location'"
            required
            maxlength="128"
            :message="error('name')"
        />

        <div class="columns">
            <div class="column is-half">
                <biz-form-select
                    v-model="space.parent_id"
                    class="is-fullwidth"
                    :label="i18n.parent"
                    :disabled="!canChangeParent"
                    :message="error('parent_id')"
                >
                    <option
                        v-for="option in parentOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.value }}
                    </option>
                </biz-form-select>
            </div>
            <div class="column is-half">
                <biz-form-select
                    v-model="space.type_id"
                    class="is-fullwidth"
                    :label="i18n.type"
                    :message="error('type_id')"
                >
                    <option
                        v-for="option in typeOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.value }}
                    </option>
                </biz-form-select>
            </div>
        </div>

        <biz-form-fieldset-location
            v-model:address="space.address"
            v-model:city="space.city"
            v-model:city-id="space.city_id"
            :initial-city="space.city_relation"
            v-model:country-code="space.country_code"
            v-model:latitude="space.latitude"
            v-model:longitude="space.longitude"
            :is-city-required="false"
            :restricted-cities="userCities"
        />

        <div class="columns">
            <div class="column is-half">
                <div class="field">
                    <biz-form-media-library
                        v-model="space.logo"
                        :label="i18n.logo"
                        image-preview-size="6"
                        :is-browse-enabled="can?.media?.browse ?? false"
                        :is-download-enabled="can?.media?.read ?? false"
                        :is-edit-enabled="can?.media?.edit ?? false"
                        :is-upload-enabled="can?.media?.add ?? false"
                        :medium="logoMedia"
                        :dimension="dimensions.logo"
                        :instructions="instructions.logoMediaLibrary"
                        :message="error('logo')"
                    />
                </div>
            </div>
            <div class="column is-half">
                <div class="field">
                    <biz-form-media-library
                        v-model="space.cover"
                        :label="i18n.cover"
                        image-preview-size="8"
                        :is-browse-enabled="can?.media?.browse ?? false"
                        :is-download-enabled="can?.media?.read ?? false"
                        :is-edit-enabled="can?.media?.edit ?? false"
                        :is-image-preview-thumbnail="false"
                        :is-upload-enabled="can?.media?.add ?? false"
                        :medium="coverMedia"
                        :dimension="dimensions.cover"
                        :instructions="instructions.coverMediaLibrary"
                        :message="error('cover')"
                    />
                </div>
            </div>
        </div>

        <div class="field">
            <biz-label>{{ i18n.contacts }}</biz-label>

            <biz-button-icon
                :icon="icon.add"
                type="button"
                @click="openContactModal"
            >
                <span>{{ i18n.add_contact }}</span>
            </biz-button-icon>
        </div>

        <div class="columns is-multiline pl-3">
            <div
                v-for="(contact, index) in space.contacts"
                :key="index"
                class="column is-4"
            >
                <biz-card>
                    <template #headerTitle>
                        {{ contact.name }}
                    </template>

                    <template #headerButton>
                        <biz-icon
                            :icon="icon.close"
                            @click="removeContact(index)"
                        />
                    </template>

                    <p>
                        {{ i18n.email }}: {{ contact.email }}
                    </p>
                    <p>
                        {{ i18n.phone }}: {{ getDial(contact.phone) }} {{ contact.phone.number }}
                    </p>
                </biz-card>
            </div>
        </div>

        <space-modal-contact
            v-if="isContactModalOpen"
            v-model="formContact"
            :contact-errors="contactErrors"
            :country-options="countryOptions"
            :default-country="defaultCountry"
            @add="addContact"
            @close="closeContactModal"
        />

        <!-- Optional Product Creation Section -->
        <div v-if="canCreateProduct" class="box mt-5">
            <div class="field">
                <biz-form-checkbox
                    v-model="space.create_product"
                    :label="i18n.create_product || 'Create a bookable product for this space'"
                />
            </div>

            <div v-if="space.create_product">
                <hr />
                
                <h3 class="title is-5 mb-4">{{ i18n.product_details || 'Product Details' }}</h3>
                
                <biz-form-input
                    v-model="space.product_name"
                    :label="i18n.product_name || 'Product Name'"
                    placeholder="e.g. Pitch Booking"
                    required
                    maxlength="255"
                    :message="error('product_name')"
                />

                <biz-form-input
                    v-model="space.product_short_description"
                    :label="i18n.product_short_description || 'Short Description'"
                    placeholder="Brief description of the product"
                    maxlength="500"
                    :message="error('product_short_description')"
                />

                <div class="field">
                    <biz-label>{{ i18n.product_description || 'Description' }}</biz-label>
                    <div class="control">
                        <textarea
                            v-model="space.product_description"
                            class="textarea"
                            rows="4"
                            maxlength="5000"
                            placeholder="Full description of the product"
                        ></textarea>
                    </div>
                    <p v-if="error('product_description')" class="help is-danger">
                        {{ error('product_description') }}
                    </p>
                </div>

                <div class="columns">
                    <div class="column is-half">
                        <biz-form-select
                            v-model="space.product_status"
                            class="is-fullwidth"
                            :label="i18n.product_status || 'Status'"
                            :message="error('product_status')"
                        >
                            <option
                                v-for="option in productStatusOptions"
                                :key="option.id"
                                :value="option.id"
                            >
                                {{ option.value }}
                            </option>
                        </biz-form-select>
                    </div>
                    <div class="column is-half">
                        <biz-form-select
                            v-model="space.product_roles"
                            class="is-fullwidth"
                            :label="i18n.product_roles || 'Visible to Role'"
                            :message="error('product_roles')"
                        >
                            <option
                                v-for="option in productRoleOptions"
                                :key="option.id"
                                :value="option.id"
                            >
                                {{ option.value }}
                            </option>
                        </biz-form-select>
                    </div>
                </div>

                <div class="field">
                    <biz-form-checkbox
                        v-model="space.product_is_check_in_required"
                        :label="i18n.product_check_in_required || 'Is check-in required?'"
                    />
                </div>

                <div class="notification is-info is-light">
                    <p>{{ i18n.product_note || 'The product will be automatically linked to this space and will inherit the space\'s location details.' }}</p>
                </div>
            </div>
        </div>

        <slot />

        <slot name="action" />
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizCard from '@/Biz/Card.vue';
    import BizFormCheckbox from '@/Biz/Checkbox.vue';
    import BizFormFieldsetLocation from '@/Biz/Form/FieldsetLocation.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizLabel from '@/Biz/Label.vue';
    import BizFormMediaLibrary from '@/Biz/Form/MediaLibrary.vue';
    import SpaceModalContact from './SpaceModalContact.vue';
    import icon from '@/Libs/icon-class';
    import { find } from 'lodash';
    import { useModelWrapper, getPhoneCountries } from '@/Libs/utils';

    export default {
        components: {
            BizButtonIcon,
            BizCard,
            BizFormCheckbox,
            BizFormFieldsetLocation,
            BizFormInput,
            BizFormMediaLibrary,
            BizFormSelect,
            BizIcon,
            BizLabel,
            SpaceModalContact,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            can: {},
            i18n: { default: () => ({
                name: 'Name',
                parent: 'Parent',
                type: 'Type',
                address: 'Address',
                latitude: 'Latitude',
                longitude: 'Longitude',
                logo: 'Logo',
                cover: 'Cover',
                contacts: 'Contacts',
                add_contact: 'Add contact',
                email: 'Email',
                phone: 'Phone',
            }) },
            dimensions: {},
        },

        props: {
            modelValue: { type: Object, required: true },
            coverMedia: { type: Object, default: () => {} },
            defaultCountry: { type: String, default: '' },
            instructions: { type: Object, default: () => {} },
            logoMedia: { type: Object, default: () => {} },
            parentOptions: { type: Object, required: true },
            typeOptions: { type: Object, required: true },
            canChangeParent: { type: Boolean, default: true },
            isCityAdmin: { type: Boolean, default: false },
            userCities: { type: Array, default: () => [] },
            canCreateProduct: { type: Boolean, default: false },
            productRoleOptions: { type: Array, default: () => [] },
            productStatusOptions: { type: Array, default: () => [] },
        },

        setup(props, { emit }) {
            return {
                space: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                formContact: this.initForm(),
                isContactModalOpen: false,
                contactErrors: {},
                icon,
                countryOptions: [],
            };
        },

        mounted: async function() {
            this.countryOptions = await getPhoneCountries();
        },

        methods: {
            openContactModal() {
                this.formContact = this.initForm();
                this.isContactModalOpen = true;
                this.contactErrors = {};
            },

            closeContactModal() {
                this.isContactModalOpen = false;
            },

            initForm() {
                return {
                    name: null,
                    phone: {},
                    email: null,
                };
            },

            addContact() {
                const self = this;
                const url = route('admin.api.spaces.contact.validate');

                axios.post(url, self.formContact)
                    .then(() => {
                        self.space.contacts.push(self.formContact);

                        self.closeContactModal();
                    })
                    .catch((error) => {
                        self.contactErrors = error.response.data.errors;
                    });
            },

            removeContact(index) {
                this.space.contacts.splice(index, 1);
            },

            getDial(phone) {
                if (phone.number) {
                    const country = find(this.countryOptions, ['id', phone.country]);

                    return country ? '+' + country.dial : '';
                }

                return "";
            },
        },
    };
</script>
