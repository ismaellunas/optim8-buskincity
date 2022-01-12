<template>
    <form @submit.prevent="submit">
        <component
            :is="field.type"
            v-for="field in schema.fields"
            :key="field.name"
            v-model="form[ field.name ]"
            :schema="field"
            :message="error(field.name, bagName, form.errors)"
        />

        <div :class="buttonGroupClass">
            <div class="control">
                <sdb-button
                    v-if="buttonLabel"
                    :class="buttonClass"
                    @click="submit"
                >
                    {{ buttonLabel }}
                </sdb-button>

                <template v-else>
                    <sdb-button
                        v-for="button in schema.buttons"
                        :key="button.label"
                        :class="buttonClass"
                        @click="submit"
                    >
                        {{ button.label }}
                    </sdb-button>
                </template>
            </div>
        </div>
    </form>
</template>

<script>
    import Checkbox from './Checkbox';
    import CheckboxGroup from './CheckboxGroup';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import Number from './Number';
    import Phone from './Phone';
    import Radio from './Radio';
    import SdbButton from '@/Sdb/Button';
    import Select from './Select';
    import Text from './Text';
    import Textarea from './Textarea';
    import { isEmpty } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'FormBuilder',

        components: {
            Checkbox,
            CheckboxGroup,
            Number,
            Phone,
            Radio,
            SdbButton,
            Select,
            Text,
            Textarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            entityId: {},
            bagName: { type: String, default: 'formBuilder' },
            buttonClass: {type: String, default: 'is-primary'},
            buttonGroupAlign: { type: String, default: 'left'},
            buttonLabel: { type: String, default: null},
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
                form: useForm({}),
                loader: null,
                schema: { fields: [] },
            };
        },

        computed: {
            buttonGroupClass() {
                return [
                    'field',
                    'is-grouped',
                    'is-grouped-' + this.buttonGroupAlign,
                ];
            },
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
                self.schema = response.data;

                self.form = self.createForm(self.schema.fields);
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
                            },
                            onError: errors => {
                                oopsAlert({isScrollToTop: false});
                            },
                            onFinish: (visit) => {
                                self.loader.hide();
                            },
                        }
                    );
            }
        },
    };
</script>
