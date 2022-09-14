<template>
    <div v-if="isShown">
        <biz-flash-notifications :flash="flash" />

        <form @submit.prevent="submit">
            <field-group
                ref="field_group"
                v-model="form"
                :group="fieldGroup"
                :errors="formErrors"
            />

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
    import FieldGroup from '@/Form/FieldGroup';
    import { isEmpty, forOwn } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { reactive } from 'vue';

    export default {
        name: 'FormBuilder',

        components: {
            BizButton,
            BizFlashNotifications,
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
        },

        data() {
            return {
                fieldGroup: {},
                flash: {
                    message: null
                },
                form: reactive({}),
                formErrors: {},
                isShown: false,
                urls: {
                    getSchemas: '/form-builders/schema',
                    save: '/form-builders/save',
                },
            };
        },

        mounted() {
            this.getSchema();
        },

        methods: {
            getSchema() {
                const self = this;

                return axios.get(
                    self.urls.getSchemas,
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

                axios.post(
                    self.urls.save,
                    self.form,
                )
                    .then((response) => {
                        successAlert('Successfully');
                        self.flash.message = response.data.message;

                        self.getSchema();
                        self.formErrors = {};
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
