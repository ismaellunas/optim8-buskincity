<template>
    <div
        v-if="isShown"
        class="generated-form-builder"
    >
        <biz-flash-notifications :flash="flash" />

        <biz-notifications
            class="is-danger"
            :message="errorMessage"
        />

        <form
            :key="formKey"
            @submit.prevent="onSubmit"
        >
            <field-group
                v-for="(group, index) in formSchema.fieldGroups"
                :key="index"
                v-model="form"
                :group="group"
                :errors="formErrors"
            />

            <biz-recaptcha
                ref="recaptcha"
                :site-key="recaptchaSiteKey"
                @on-verify="recaptchaVerify"
            />

            <slot name="buttons">
                <div
                    class="field"
                    :class="containerButtonClasses"
                >
                    <biz-button class="is-medium is-primary">
                        <span class="has-text-weight-bold">
                            {{ submitButtonText }}
                        </span>
                    </biz-button>
                </div>
            </slot>
        </form>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import { inRange, isEmpty, forOwn } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { reactive, defineAsyncComponent } from 'vue';
    import { serialize } from 'object-to-formdata';

    export default {
        name: 'FormBuilder',

        components: {
            BizButton: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/Button.vue')
            ),
            BizFlashNotifications: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/FlashNotifications.vue')
            ),
            BizNotifications: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/Notifications.vue')
            ),
            BizRecaptcha: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/Recaptcha.vue')
            ),
            FieldGroup: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Form/FieldGroup.vue')
            ),
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
                formSchema: {},
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
                formKey: 0,
                submitLoader: null,
            };
        },

        computed: {
            submitButtonText() {
                return this.formSchema?.button?.text
                    ?? 'Submit';
            },

            containerButtonClasses() {
                let classes = [];

                return classes.concat([
                    (this.formSchema?.button?.position ?? null),
                ]).filter(Boolean);
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
                    self.formSchema = response.data;

                    self.form = self.createForm(self.formSchema);

                    self.isShown = true;

                    if (isEmpty(this.formSchema)) {
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

                forOwn(this.formSchema.fieldGroups, (groupField, key) => {

                    if (!isEmpty(groupField.fields)) {

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
                });

                return reactive(form);
            },

            async onSubmit() {
                this.submitLoader = this.$loading.show();

                try {
                    await this.$refs.recaptcha.execute();
                } catch (e) {
                    console.error("Invalid Recaptcha key");
                } finally {
                    this.submitLoader.hide();
                }
            },

            recaptchaVerify(response = null) {
                const self = this;

                self.form['g-recaptcha-response'] = response;

                let formData = serialize(self.form);

                const config = {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                };

                self.onStartLoadingOverlay();

                self.flash.message = null;

                axios.post(
                    self.urls.save,
                    formData,
                    config
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
                        oopsAlert({
                            text: 'There are errors in the form. Please check the fields marked in red for more information.'
                        });

                        self.formErrors = error.response.data.errors;
                        self.errorMessage = error.response.data.message;
                    })
                    .then(() => {
                        self.onEndLoadingOverlay();

                        self.formKey += 1;
                    });
            },
        },
    };
</script>
