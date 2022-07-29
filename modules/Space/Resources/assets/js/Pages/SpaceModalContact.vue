<template>
    <biz-modal-card
        class="wrapper-modal"
        :is-close-hidden="true"
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title">
                Contact
            </p>

            <biz-button
                aria-label="close"
                class="delete is-primary"
                type="button"
                @click="$emit('close')"
            />
        </template>

        <form @submit.prevent="addContact">
            <div class="columns">
                <div class="column is-half">
                    <biz-form-input
                        v-model="contact.name"
                        label="Name"
                        required
                        maxlength="128"
                        :message="error('name', null, contactErrors)"
                    />

                    <biz-form-input
                        v-model="contact.email"
                        label="Email"
                        maxlength="255"
                        :message="error('email', null, contactErrors)"
                    />
                </div>

                <div class="column is-half">
                    <biz-form-phone
                        v-model="contact.phone"
                        label="Phone"
                        :country-options="countryOptions"
                        :default-country="defaultCountry"
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
                    @click="$emit('add')"
                >
                    Save
                </a>
                <a
                    class="button"
                    type="button"
                    @click="$emit('close')"
                >
                    Cancel
                </a>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormPhone from '@/Biz/Form/Phone';
    import BizModalCard from '@/Biz/ModalCard';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizButton,
            BizFormInput,
            BizFormPhone,
            BizModalCard,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            contactErrors: { type: Object, default: () => {} },
            countryOptions: { type: Array, default: () => [] },
            defaultCountry: { type: String, default: undefined },
            modelValue: { type: Object, required: true },
        },

        emits: [
            'add',
            'close',
        ],

        setup(props, { emit }) {
            return {
                contact: useModelWrapper(props, emit),
            };
        },
    };
</script>
