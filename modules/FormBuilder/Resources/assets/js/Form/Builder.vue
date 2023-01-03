<template>
    <div v-if="isShown">
        <biz-flash-notifications :flash="flash" />
        <biz-notifications
            class="is-danger"
            :message="errorMessage"
        />

        <form @submit.prevent="onSubmit">
            <field-group
                ref="field_group"
                v-model="form"
                :group="fieldGroup"
                :errors="formErrors"
            />

            <biz-recaptcha
                ref="recaptcha"
                :site-key="recaptchaSiteKey"
                @on-verify="recaptchaVerify"
            />

            <slot name="buttons">
                <div class="field">
                    <biz-button class="is-medium is-primary">
                        <span class="has-text-weight-bold">
                            {{ submitLabel }}
                        </span>
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
    import BizRecaptcha from '@/Biz/Recaptcha';
    import FieldGroup from '@/Form/FieldGroup';
    import { inRange, isEmpty, forOwn } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { reactive } from 'vue';

    export default {
        name: 'FormBuilder',

        components: {
            BizButton,
            BizFlashNotifications,
            BizNotifications,
            BizRecaptcha,
            FieldGroup,
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
            recaptchaSiteKey: { type: [String, null], default: null }
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
                urls: {
                    getSchema: '/form-builders/schema',
                    save: '/form-builders/save',
                },
            };
        },

        computed: {
            submitLabel() {
                return this.fieldGroup?.buttons?.submit?.label;
            },

            isRecaptchaAvailable() {
                return !!this.recaptchaSiteKey;
            },
        },

        mounted() {
            this.getSchema();
        },

        methods: {
            getSchema() {
                const self = this;

                return axios.get(
                    self.urls.getSchema,
                    {
                        params: {
                            form_id: self.formId,
                        }
                    }

                ).then((response) => {
                    self.fieldGroup = response.data;

                    self.form = self.createForm(self.fieldGroup);

                    self.isShown = true;

                    if (isEmpty(this.fieldGroup)) {
                        self.isShown = false;
                    }

                }).catch((error) => {
                    if (error.response) {
                        if (inRange(error.response.status, 399, 600)) {
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

            onSubmit() {
                this.$refs.recaptcha.execute();
            },

            recaptchaVerify(response = null) {
                const self = this;

                self.onStartLoadingOverlay();

                self.form['g-recaptcha-response'] = response;

                axios.post(
                    self.urls.save,
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
                    });
            },
        },
    };
</script>