<template>
    <div v-if="isShown">
        <biz-flash-notifications :flash="flash" />
        <biz-notifications
            class="is-danger"
            :message="errorMessage"
        />

        <form @submit.prevent="submit">
            <field-group
                ref="field_group"
                v-model="form"
                :group="fieldGroup"
                :errors="formErrors"
            />

            <vue-recaptcha
                ref="vueRecaptcha"
                :sitekey="recaptchaSiteKey"
                size="invisible"
                theme="light"
                @expired="recaptchaExpired"
                @error="recaptchaFailed"
                @verify="recaptchaVerify"
            />

            <span
                v-if="isRecaptchaError"
                class="has-text-danger"
            >
                Please check the captcha!
            </span>

            <slot name="buttons">
                <div class="field">
                    <biz-button class="is-medium is-primary">
                        <span class="has-text-weight-bold">Submit</span>
                    </biz-button>
                </div>
            </slot>
        </form>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button';
    import BizFlashNotifications from '@/Biz/FlashNotifications';
    import BizNotifications from '@/Biz/Notifications';
    import FieldGroup from '@/Form/FieldGroup';
    import { VueRecaptcha } from 'vue-recaptcha';
    import { isEmpty, forOwn } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { recaptchaSiteKey } from '@/Libs/defaults';
    import { reactive } from 'vue';

    export default {
        name: 'FormBuilder',

        components: {
            BizButton,
            BizFlashNotifications,
            BizNotifications,
            FieldGroup,
            VueRecaptcha,
        },

        mixins: [
            MixinHasLoader,
        ],

        provide() {
            return {
                bagName: this.bagName,
            };
        },

        props: {
            bagName: { type: String, default: null },
            formId: { type: [String, null], required: true },
            routeGetSchema: { type: String, default: 'form-builders.schema' },
        },

        data() {
            return {
                errorMessage: null,
                fieldGroup: {},
                flash: {
                    message: null
                },
                form: reactive({}),
                formErrors: {},
                isShown: false,
                isRecaptchaError: false,
                recaptchaResponse: null,
                recaptchaSiteKey,
            };
        },

        mounted() {
            this.getSchema();
        },

        methods: {
            recaptchaExpired() {
                this.$refs.vueRecaptcha.reset();
            },

            recaptchaFailed() {
                this.isRecaptchaError = true;
            },

            recaptchaVerify(response) {
                this.recaptchaResponse = response;
            },

            getSchema() {
                const self = this;

                return axios.get(
                    route(self.routeGetSchema),
                    {
                        params: {
                            form_id: self.formId,
                        }
                    }

                ).then((response) => {
                    self.fieldGroup = response.data;

                    self.form = self.createForm(self.fieldGroup);

                    self.isShown = true;

                    setTimeout(() => {
                        self.$refs.vueRecaptcha.execute();
                    }, 500);

                    if (isEmpty(this.fieldGroup)) {
                        self.isShown = false;
                    }

                }).catch((error) => {
                    if (error.response) {
                        if (error.response.status == 403) {
                            self.isShown = false;
                        }
                    }
                });
            },

            createForm(groupField) {
                let fieldValue = null;
                const form = {};

                form['form_id'] = this.formId;

                if (!isEmpty(groupField)) {
                    forOwn(groupField.fields, (field, key) => {
                        if (typeof field.value === 'undefined') {
                            form[ key ] = undefined;
                        } else {
                            form[ key ] = field.value;

                            if (field.is_translated && field.value.length == 0) {
                                fieldValue = {};

                                this.localeOptions.forEach(function(locale) {
                                    fieldValue[ locale.id ] = null
                                })

                                form[ key ] = fieldValue;
                            }
                        }
                    });
                }

                return reactive(form);
            },

            submit() {
                const self = this;

                self.onStartLoadingOverlay();

                self.form['g-recaptcha-response'] = self.recaptchaResponse;

                axios.post(
                    route('form-builders.save'),
                    self.form,
                )
                    .then((response) => {
                        let data = response.data;

                        if (data.success) {
                            successAlert('Successfully');
                            self.flash.message = data.message;

                            self.getSchema();
                            self.formErrors = {};
                            self.errorMessage = null;
                        } else {
                            self.errorMessage = data.message;
                        }
                    })
                    .catch((error) => {
                        oopsAlert();

                        self.formErrors = error.response.data.errors;
                    })
                    .then(() => {
                        self.onEndLoadingOverlay();
                    })
            },
        },
    };
</script>
