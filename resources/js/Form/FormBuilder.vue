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

        <sdb-button
            v-for="button in schema.buttons"
            :key="button.label"
            class="is-primary"
            @click="submit"
        >
            {{ button.label }}
        </sdb-button>
    </form>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
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
            SdbButton,
            Select,
            Text,
            Textarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            name: { type: String, required: true },
            bagName: { type: String, default: 'formBuilder' },
        },

        setup(props) {
            return {};
        },

        data() {
            return {
                form: useForm({}),
                loader: null,
                schema: { fields: [] },
            };
        },

        mounted() {
            const self = this;

            axios.get(route("forms.schema", self.name))
                .then((response) => {
                    self.schema = response.data;

                    self.form = self.createForm(self.schema.fields);
                });
        },

        methods: {
            createForm(fields) {
                const form = {};

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

                this.form.post(
                    route('forms.save', self.name),
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
                )
            }
        },
    };
</script>
