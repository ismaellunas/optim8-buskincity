<template>
    <div>
        <biz-form-input
            v-model="space.name"
            :label="i18n.name"
            placeholder="e.g My Location"
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
            v-model:country-code="space.country_code"
            v-model:latitude="space.latitude"
            v-model:longitude="space.longitude"
            :is-city-required="false"
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

        <slot />

        <slot name="action" />
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizCard from '@/Biz/Card.vue';
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
