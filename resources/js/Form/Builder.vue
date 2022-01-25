<template>
    <form @submit.prevent="submit">
        <field-group
            v-for="(group, index) in sortedFieldGroups"
            :key="index"
            :ref="'field_group__'+index"
            v-model="form"
            :group="group"
        />

        <slot
            name="buttons"
            :submit="submit"
        >
            <div class="field is-grouped is-grouped-left">
                <div class="control">
                    <biz-button
                        class="is-primary"
                        @click="submit"
                    >
                        Submit
                    </biz-button>
                </div>
            </div>
        </slot>
    </form>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import FieldGroup from './FieldGroup';
    import { isEmpty, forOwn, sortBy, forEach } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

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
            entityId: {type: Number, default: null},
            bagName: { type: String, default: 'formBuilder' },
            routeName: { type: String, required: true },
            routeGetSchemas: { type: String, default: 'forms.schemas' },
            routeSave: { type: String, default: 'forms.save' },
        },

        emits: [
            'loaded-forbidden',
            'loaded-successfully'
        ],

        data() {
            return {
                fieldGroups: {},
                form: useForm({}),
                loader: null,
            };
        },

        computed: {
            sortedFieldGroups() {
                return sortBy(this.fieldGroups, ['order']);
            }
        },

        mounted() {
            const self = this;

            axios.get(
                route(self.routeGetSchemas),
                {
                    params: {
                        id: self.entityId,
                        route_name: self.routeName
                    }
                }

            ).then((response) => {
                self.fieldGroups = response.data;

                self.form = self.createForm(self.fieldGroups);

                self.$emit('loaded-successfully', response.data);

            }).catch((error) => {
                if (error.response) {
                    if (error.response.status == 403) {
                        self.$emit('loaded-forbidden', error.response);
                    }
                }
            });
        },

        methods: {
            createForm(groupFields) {
                const form = {
                    id: this.entityId
                };

                forOwn(groupFields, (groupField, key) => {

                    if (!isEmpty(groupField.fields)) {

                        forOwn(groupField.fields, (field, key) => {
                            if (typeof field.value === 'undefined') {
                                form[ key ] = undefined;
                            } else {
                                form[ key ] = field.value;
                            }
                        });
                    }
                });

                return useForm(form);
            },

            /*
            createForm(fields) {
                const form = {
                    id: this.entityId
                };

                if (!isEmpty(fields)) {
                    for (const [key, field] of Object.entries(fields)) {
                        if (typeof field.value === 'undefined') {
                            form[ key ] = undefined;
                        } else {
                            form[ key ] = field.value;
                        }
                    }
                }

                return useForm(form);
            },
            */

            submit() {
                const self = this;

                this
                    .form
                    .transform((data) => ({
                        ...data,
                        route_name: self.routeName,
                    }))
                    .post(
                        route(self.routeSave),
                        {
                            preserveScroll: true,
                            onStart: () => {
                                self.loader = self.$loading.show();
                            },
                            onSuccess: (page) => {
                                successAlert(page.props.flash.message);

                                self.resetFields();

                            },
                            onError: errors => {
                                oopsAlert({isScrollToTop: false});
                            },
                            onFinish: (visit) => {
                                self.loader.hide();
                            },
                        }
                    );
            },

            resetFields() {
                forEach(this.$refs, (fieldGroup, fieldGroupKey) => {
                    forEach(fieldGroup.$refs, (field, fieldKey) => {
                        if (field.reset) {
                            field.reset();
                        }
                    });
                });
            }
        },
    };
</script>
