<template>
    <div class="generated-form-builder">
        <biz-flash-notifications :flash="flash" />

        <biz-notifications
            class="is-danger"
            :message="errorMessage"
        />

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
    import { cloneDeep } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { defineAsyncComponent } from 'vue';
    import { serialize } from 'object-to-formdata';

    export default {
        name: 'FormBuilderSlotable',

        components: {
            BizFlashNotifications: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/FlashNotifications.vue')
            ),
            BizNotifications: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/Notifications.vue')
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

        setup(props, { emit }) {
            const resetForm = () => {
                return {
                    ...{ 'form_id': props.formId },
                    ...cloneDeep(props.formStructure)
                };
            }

            const form = resetForm();

            return {
                form,
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
                        oopsAlert({
                            text: 'There are errors in the form. Please check the fields marked in red for more information.'
                        });

                        this.formErrors = error.response.data.errors;
                        this.errorMessage = error.response.data.message;
                    })
                    .then(() => {
                        this.onEndLoadingOverlay();
                    });
            },
        },
    };
</script>
