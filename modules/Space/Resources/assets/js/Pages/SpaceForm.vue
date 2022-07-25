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

        <biz-form-text-editor
            v-model="space.contact"
            label="Contact"
            placeholder="Contact"
            :message="error('contact')"
        />

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
                v-for="contact, index in space.contacts"
                :key="index"
                class="column is-4"
            >
                <biz-card>
                    <p>
                        Name: {{ formContact.name }}
                    </p>
                    <p>
                        Email: {{ formContact.email }}
                    </p>
                    <p>
                        Phone: {{ formContact.phone.number }}
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
                            :message="formContact.errors.name"
                        />

                        <biz-form-input
                            v-model="formContact.email"
                            label="Email"
                            maxlength="255"
                            :message="formContact.errors.email"
                        />
                    </div>

                    <div class="column is-half">
                        <biz-form-phone
                            v-model="formContact.phone"
                            label="Phone"
                            :country-options="countryOptions"
                            :default-country="defaultCountry"
                            :dropdown-max-height="180"
                            :dropdown-max-width="280"
                            :message="formContact.errors['phone.number'] ?? ''"
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
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormPhone from '@/Biz/Form/Phone';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizFormTextEditor from '@/Biz/Form/TextEditor';
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import BizLabel from '@/Biz/Label';
    import BizModalCard from '@/Biz/ModalCard';
    import BizButton from '@/Biz/Button';
    import BizCard from '@/Biz/Card';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizFormInput,
            BizFormPhone,
            BizFormSelect,
            BizFormTextEditor,
            BizFormTextarea,
            BizLabel,
            BizModalCard,
            BizButton,
            BizCard,
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
            };
        },

        methods: {
            openContactModal() {
                this.formContact = this.initForm();
                this.isContactModalOpen = true;
            },
            closeContactModal() {
                this.isContactModalOpen = false;
            },

            initForm() {
                return useForm({
                    name: null,
                    phone: {},
                    email: null,
                });
            },

            addContact() {
                const self = this;

                self.formContact.post(
                    route('admin.api.spaces.contact.validate'),
                    {
                        errorBag: 'contactValidation',
                        onSuccess: page => {
                            self.space.contacts.push({
                                name: formContact.name,
                                phone: formContact.phone,
                                email: formContact.email,
                            })

                            self.closeContactModal();
                        },
                    }
                );
            },
        },
    };
</script>
