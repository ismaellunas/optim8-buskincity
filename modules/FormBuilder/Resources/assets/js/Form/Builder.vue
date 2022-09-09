<template>
    <div v-if="isShown">
        <form @submit.prevent="submit">
            <field-group
                ref="field_group"
                v-model="form"
                :group="fieldGroup"
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
    import BizButton from '@/Biz/Button';
    import FieldGroup from '@/Form/FieldGroup';
    import { isEmpty, forOwn, sortBy, forEach, find } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import { reactive } from 'vue';

    export default {
        name: 'FormBuilder',

        components: {
            BizButton,
            FieldGroup,
        },

        provide() {
            return {
                bagName: this.bagName,
            };
        },

        props: {
            bagName: { type: String, default: 'formBuilder' },
            formId: { type: [String, null], required: true },
            routeGetSchema: { type: String, default: 'form-builders.schema' },
        },

        data() {
            return {
                form: useForm({}),
                loader: null,
                fieldGroup: {},
                form: reactive({}),
                isShown: false,
            };
        },

        mounted() {
            this.getSchema();
        },

        methods: {
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

            resetFields() {
                forEach(this.$refs, (fieldGroup, fieldGroupKey) => {
                    forEach(fieldGroup.$refs, (field, fieldKey) => {
                        if (field.reset) {
                            field.reset();
                        }
                    });
                });

                this.formErrors = {};
            },

            submit() {
                const self = this;

                self.onStartLoadingOverlay();

                axios.post(
                    route('form-builders.save'),
                    this.form,
                )
                    .then((response) => {
                        successAlert('Successfully');
                        self.flash.message = response.data.message;

                        self.getSchema();
                        self.resetFields();
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
