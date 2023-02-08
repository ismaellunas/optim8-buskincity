<template>
    <div>
        <biz-form-input
            v-model="space.name"
            label="Name"
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
                    label="Parent"
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
                    label="Type"
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

        <biz-form-textarea
            v-model="space.address"
            label="Address"
            placeholder="Address"
            rows="2"
            maxlength="500"
            :message="error('address')"
        />

        <div class="columns">
            <div class="column is-half">
                <biz-form-input
                    v-model="space.latitude"
                    label="Latitude"
                    :message="error('latitude')"
                />
            </div>

            <div class="column is-half">
                <biz-form-input
                    v-model="space.longitude"
                    label="Longitude"
                    :message="error('longitude')"
                />
            </div>
        </div>

        <div class="columns">
            <div class="column is-half">
                <div class="field">
                    <biz-label>Logo</biz-label>

                    <biz-image
                        v-if="logoDisplay"
                        :src="logoDisplay"
                        :img-style="{'max-width': '300px', 'max-height': '300px'}"
                    />

                    <biz-button-icon
                        v-if="logoDisplay"
                        class="is-small is-danger"
                        type="button"
                        :icon="icon.remove"
                        @click="deleteLogo"
                    >
                        <span>Delete</span>
                    </biz-button-icon>

                    <biz-form-file
                        v-model="space.logo"
                        :is-name-displayed="false"
                        :message="error('logo')"
                        :notes="instructions.logo"
                        @on-file-picked="onFilePicked"
                    />
                </div>
            </div>
            <div class="column is-half">
                <div class="field">
                    <biz-label>Cover</biz-label>

                    <biz-image
                        v-if="coverDisplay"
                        :src="coverDisplay"
                        :img-style="{'max-width': '600px', 'max-height': '400px'}"
                    />

                    <biz-button-icon
                        v-if="coverDisplay"
                        class="is-small is-danger"
                        type="button"
                        :icon="icon.remove"
                        @click="deleteCover"
                    >
                        <span>Delete</span>
                    </biz-button-icon>

                    <biz-form-file
                        v-model="space.cover"
                        :is-name-displayed="false"
                        :message="error('cover')"
                        :notes="instructions.cover"
                        @on-file-picked="onCoverFilePicked"
                    />
                </div>
            </div>
        </div>

        <div class="field">
            <biz-label>Contacts</biz-label>

            <biz-button-icon
                :icon="icon.add"
                type="button"
                @click="openContactModal"
            >
                <span>Add Contact</span>
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
                        Email: {{ contact.email }}
                    </p>
                    <p>
                        Phone: {{ getDial(contact.phone) }} {{ contact.phone.number }}
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
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizCard from '@/Biz/Card';
    import BizFormFile from '@/Biz/Form/File';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import BizIcon from '@/Biz/Icon';
    import BizImage from '@/Biz/Image';
    import BizLabel from '@/Biz/Label';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SpaceModalContact from './SpaceModalContact';
    import icon from '@/Libs/icon-class';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { find, set, unset } from 'lodash';
    import { useModelWrapper, getPhoneCountries } from '@/Libs/utils';

    export default {
        components: {
            BizButtonIcon,
            BizCard,
            BizFormFile,
            BizFormInput,
            BizFormSelect,
            BizFormTextarea,
            BizIcon,
            BizImage,
            BizLabel,
            SpaceModalContact,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            modelValue: { type: Object, required: true },
            coverUrl: { type: [String, null], default: '' },
            defaultCountry: { type: String, default: '' },
            instructions: { type: Object, default: () => {} },
            logoUrl: { type: [String, null], default: '' },
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
                logoSrc: this.logoUrl,
                coverSrc: this.coverUrl,
                countryOptions: [],
            };
        },

        computed: {
            logoDisplay() {
                return this.logoSrc ?? null;
            },

            coverDisplay() {
                return this.coverSrc ?? null;
            },
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

            onFilePicked(event) {
                this.logoSrc = event.target.result;

                unset(this.space, 'deleted_media.logo');
            },

            onCoverFilePicked(event) {
                this.coverSrc = event.target.result;

                unset(this.space, 'deleted_media.cover');
            },

            deleteLogo() {
                this.space.logo = null;
                this.logoSrc = null;

                set(this.space, 'deleted_media.logo', true);
            },

            deleteCover() {
                this.space.cover = null;
                this.coverSrc = null;

                set(this.space, 'deleted_media.cover', true);
            }
        },
    };
</script>
