<template>
    <biz-modal-card
        class="wrapper-modal"
        :is-close-hidden="true"
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title">
                {{ i18n.contact }}
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
                        :label="i18n.name"
                        required
                        maxlength="128"
                        :message="error('name', null, contactErrors)"
                    />

                    <biz-form-input
                        v-model="contact.email"
                        :label="i18n.email"
                        maxlength="255"
                        :message="error('email', null, contactErrors)"
                    />
                </div>

                <div class="column is-half">
                    <biz-form-phone
                        v-model="contact.phone"
                        :label="i18n.phone"
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
                    {{ i18n.save }}
                </a>
                <a
                    class="button"
                    type="button"
                    @click="$emit('close')"
                >
                    {{ i18n.cancel }}
                </a>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormPhone from '@/Biz/Form/Phone.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
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

        inject: {
            i18n: { default: () => ({
                contact: 'Contact',
                email: 'Email',
                phone: 'Phone',
                cancel: 'Cancel',
                save: 'Save',
            }) },
        },

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
