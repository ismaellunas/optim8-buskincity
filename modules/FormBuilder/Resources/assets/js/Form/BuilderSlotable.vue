<template>
    <div class="generated-form-builder">
        <biz-flash-notifications :flash="flash" />

        <biz-error-notifications :errors="errorMessage" />

        <form
            :key="formKey"
            @submit.prevent="onSubmit"
        >
            <biz-recaptcha
                ref="recaptcha"
                :site-key="recaptchaSiteKey"
                @on-verify="recaptchaVerify"
            />

            <slot
                :form="form"
                :get-error="error"
                :form-errors="formErrors"
            />
        </form>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { cloneDeep, pickBy } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { defineAsyncComponent, ref } from 'vue';
    import { serialize } from 'object-to-formdata';

    export default {
        name: 'FormBuilderSlotable',

        components: {
            BizFlashNotifications: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/FlashNotifications.vue')
            ),
            BizErrorNotifications: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/ErrorNotifications.vue')
            ),
            BizRecaptcha: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/Recaptcha.vue')
            ),
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        provide() {
            return {
                bagName: this.bagName,
            };
        },

        props: {
            bagName: { type: String, default: null },
            formId: { type: [String, null], required: true },
            recaptchaSiteKey: { type: [String, null], default: null },
            formStructure: { type: Object, required: true},
        },

        setup(props) {
            const resetForm = () => {
                return ref({
                    ...{ 'form_id': props.formId },
                    ...cloneDeep(props.formStructure)
                });
            }

            return {
                form: resetForm(),
                resetForm,
            };
        },

        data() {
            return {
                errorMessage: null,
                formSchema: {},
                flash: {
                    message: null
                },
                formErrors: {},
                urls: {
                    getSchema: '/form-builders/schema',
                    save: '/form-builders/save',
                },
                formKey: 0,
            };
        },

        computed: {
            submitButtonText() {
                return this.formSchema?.button?.text
                    ?? 'Submit';
            },

            isRecaptchaAvailable() {
                return !!this.recaptchaSiteKey;
            },
        },

        methods: {
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

                            self.formErrors = {};
                            self.errorMessage = null;
                        } else {
                            self.errorMessage = data.message;
                        }

                        this.form = self.resetForm();

                        this.formKey += 1;
                    })
                    .catch((error) => {
                        this.formErrors = error.response.data.errors;
                        this.errorMessage = this.getErrorMessage(error.response.data.errors);

                        oopsAlert({
                            text: this.formErrors?.default[0] ?? null
                        });

                        this.showErrorFields();
                    })
                    .then(() => {
                        this.onEndLoadingOverlay();
                    });
            },

            showErrorFields() {
                Object.keys(this.formErrors).forEach((key) => {
                    const index = key.indexOf(".");

                    const suffix = (index === -1) ? key : key.substring(0, index);

                    const element = this.$el.querySelector('.form-input-'+suffix)

                    if (element) {
                        element.classList.add('is-danger');
                    }
                });
            },

            getErrorMessage(errors) {
                return {
                    form_builder: pickBy(errors, (value, key) => !['default'].includes(key)),
                };
            },
        },
    };
</script>
