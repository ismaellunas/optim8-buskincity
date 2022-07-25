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
                    :disabled="!!space.id"
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
                    v-model="space.type"
                    class="is-fullwidth"
                    label="Type"
                    :message="error('type')"
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

        <div class="field">
            <biz-label>Contact</biz-label>

            <button
                class="button"
                type="button"
                @click.prevent="openContactModal"
            >
                Add Contact
            </button>
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
                        Phone: {{ contact.phone.number }}
                    </p>
                </biz-card>
            </div>
        </div>

        <biz-modal-card
            v-if="isContactModalOpen"
            class="wrapper-modal"
            :is-close-hidden="true"
            @close="closeContactModal"
        >
            <template #header>
                <p class="modal-card-title">
                    Contact
                </p>

                <biz-button
                    aria-label="close"
                    class="delete is-primary"
                    type="button"
                    @click="closeContactModal"
                />
            </template>

            <form @submit.prevent="addContact">
                <div class="columns">
                    <div class="column is-half">
                        <biz-form-input
                            v-model="formContact.name"
                            label="Name"
                            required
                            maxlength="128"
                            :message="error('name', null, contactErrors)"
                        />

                        <biz-form-input
                            v-model="formContact.email"
                            label="Email"
                            maxlength="255"
                            :message="error('email', null, contactErrors)"
                        />
                    </div>

                    <div class="column is-half">
                        <biz-form-phone
                            v-model="formContact.phone"
                            label="Phone"
                            :country-options="countryOptions"
                            :dropdown-max-height="180"
                            :dropdown-max-width="280"
                            :message="error('phone.number', null, contactErrors)"
                        />
                    </div>
                </div>
            </form>

            <template #footer>
                <div class="buttons is-right">
                    <a
                        class="button is-link"
                        type="button"
                        @click.prevent="addContact"
                    >
                        Save
                    </a>
                    <a
                        class="button"
                        type="button"
                        @click.prevent="closeContactModal"
                    >
                        Cancel
                    </a>
                </div>
            </template>
        </biz-modal-card>

        <slot />

        <slot name="action" />
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizCard from '@/Biz/Card';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormPhone from '@/Biz/Form/Phone';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import BizIcon from '@/Biz/Icon';
    import BizLabel from '@/Biz/Label';
    import BizModalCard from '@/Biz/ModalCard';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import icon from '@/Libs/icon-class';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizButton,
            BizCard,
            BizFormInput,
            BizFormPhone,
            BizFormSelect,
            BizFormTextarea,
            BizIcon,
            BizLabel,
            BizModalCard,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            modelValue: { type: Object, required: true },
            parentOptions: { type: Object, required: true },
            typeOptions: { type: Object, required: true },
            countryOptions: { type: Array, default: () => [] },
            defaultCountry: { type: String, default: '' },
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
            };
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
        },
    };
</script>
