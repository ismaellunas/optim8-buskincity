<template>
    <div>
        <div class="tabs is-toggle">
            <ul>
                <li
                    v-for="(option, index) in localeOptions"
                    :key="option.id"
                    :class="{ 'is-active': option.id == selectedLocale }"
                    @click="selectedLocale = option.id"
                >
                    <a>
                        <span>{{ option.name }}</span>
                        <span
                            v-if="index == 0"
                            class="tag is-link is-light ml-3"
                        >
                            Default
                        </span>
                    </a>
                </li>
            </ul>
        </div>

        <biz-error-notifications
            :bags="[bagName]"
            :errors="$page.props.errors"
        />

        <form @submit.prevent="submit">
            <field-group
                v-for="(group, index) in sortedFieldGroups"
                :key="index"
                :ref="'field_group__'+index"
                v-model="form"
                :group="group"
                :selected-locale="selectedLocale"
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
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import FieldGroup from './FieldGroup';
    import { isEmpty, forOwn, sortBy, forEach, find } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import { ref } from 'vue';

    export default {
        name: 'FormBuilder',

        components: {
            BizButton,
            BizErrorNotifications,
            FieldGroup,
        },

        provide() {
            return {
                bagName: this.bagName,
            };
        },

        props: {
            bagName: { type: String, default: 'formBuilder' },
            entityId: {type: Number, default: null},
            locale: { type: String, default: null },
            routeGetSchemas: { type: String, default: 'forms.schemas' },
            routeName: { type: String, required: true },
            routeSave: { type: String, default: 'forms.save' },
        },

        emits: [
            'loaded-empty-field',
            'loaded-forbidden',
            'loaded-successfully'
        ],

        setup(props) {
            const defaultLocale = usePage().props.value.defaultLanguage;
            let selectedLocale = props.locale ?? defaultLocale;

            const localeOptions = sortBy(
                usePage().props.value.languageOptions,
                [
                    function(locale) {
                        return locale.id != selectedLocale;
                    }
                ]
            );

            if (typeof find(localeOptions, { 'id': selectedLocale }) === 'undefined') {
                selectedLocale = defaultLocale;
            }

            return {
                localeOptions: localeOptions,
                selectedLocale: ref(selectedLocale),
            };
        },

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
                            id: self.entityId,
                            route_name: self.routeName
                        }
                    }

                ).then((response) => {
                    self.fieldGroups = response.data;

                    self.form = self.createForm(self.fieldGroups);

                    self.$emit('loaded-successfully', response.data);

                    if (isEmpty(this.fieldGroups)) {
                        self.$emit('loaded-empty-field');
                    }

                }).catch((error) => {
                    if (error.response) {
                        if (error.response.status == 403) {
                            self.$emit('loaded-forbidden', error.response);
                        }
                    }
                });
            },

            createForm(groupFields) {
                let fieldValue = null;
                const form = {
                    id: this.entityId,
                    _token: usePage().props.value.csrfToken,
                };

                forOwn(groupFields, (groupField, key) => {

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

                return useForm(form);
            },

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
                            onSuccess: async (page) => {
                                successAlert(page.props.flash.message);

                                await self.getSchemas();

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
