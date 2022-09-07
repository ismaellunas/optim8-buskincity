<template>
    <div v-if="isShown">
        <form @submit.prevent="submit">
            <field-group
                v-for="(group, index) in sortedFieldGroups"
                :key="index"
                :ref="'field_group__'+index"
                v-model="form"
                :group="group"
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
    import { ref } from 'vue';

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
            routeGetSchemas: { type: String, default: 'form-builders.schemas' },
            formId: { type: [String, null], required: true },
        },

        data() {
            return {
                fieldGroups: {},
                form: useForm({}),
                loader: null,
                isShown: false,
            };
        },

        computed: {
            sortedFieldGroups() {
                return sortBy(this.fieldGroups, ['order']);
            },
        },

        mounted() {
            this.getSchemas();
        },

        methods: {
            getSchemas() {
                const self = this;

                return axios.get(
                    route(self.routeGetSchemas),
                    {
                        params: {
                            form_id: self.formId,
                        }
                    }

                ).then((response) => {
                    self.fieldGroups = response.data;

                    self.form = self.createForm(self.fieldGroups);

                    self.isShown = true;

                    if (isEmpty(this.fieldGroups)) {
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

            createForm(groupFields) {
                let fieldValue = null;
                const form = {};

                forOwn(groupFields, (groupField, key) => {
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
                });

                return useForm(form);
            },

            submit() {
                //
            },
        },
    };
</script>
