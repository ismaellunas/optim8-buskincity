<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <sdb-error-notifications
            :errors="$page.props.errors"
        />

        <div class="box mb-6">
            <form
                method="post"
                @submit.prevent="onSubmit"
            >
                <div class="columns">
                    <div class="column">
                        <h2><b>Additional Code</b></h2>
                    </div>
                    <div class="column">
                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <sdb-button class="is-link">
                                    Save
                                </sdb-button>
                            </div>
                        </div>
                    </div>
                </div>

                <fieldset :disabled="isProcessing">
                    <div
                        v-for="additionalCode in sortedAdditionalCodes"
                        :key="additionalCode.key"
                        class="columns"
                    >
                        <div class="column is-half">
                            <h3><b>{{ additionalCode.display_name }}</b></h3>
                        </div>
                        <div class="column">
                            <sdb-textarea
                                v-model="form[ additionalCode.key ]"
                            />
                            <p v-if="false">
                                <sdb-input-error
                                    :message="error(additionalCode.key)"
                                />
                            </p>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbTextarea from '@/Sdb/Textarea';
    import SdbInputError from '@/Sdb/InputError';
    import { forEach, has, isEmpty, mapValues, sortBy } from 'lodash';
    import { confirm as confirmAlert, success as successAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'ThemeOptionAdvance',

        components: {
            AppLayout,
            SdbButton,
            SdbErrorNotifications,
            SdbInputError,
            SdbTextarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            baseRouteName: {type: String, default: 'admin.theme.advance'},
            additionalCodes: {type: Object, default: () => {}},
            errors: {type: Object, default: () => {}},
            title: {type: String, default: 'Theme'},
        },

        setup(props) {
            const form = mapValues(
                props.additionalCodes,
                (additionalCode) => {
                    return additionalCode.value;
                }
            );

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                isProcessing: false,
                loader: null,
            };
        },

        computed: {
            sortedAdditionalCodes()
            {
                return sortBy(this.additionalCodes, ['order']);
            }
        },

        methods: {
            onSubmit() {
                const self = this;
                this.form.post(route(this.baseRouteName+'.update'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.form.isDirty = false;
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;
                    }
                });
            },
            resetForm() {
                const self = this;
                confirmAlert('Are you sure you want to reset?')
                    .then((result) => {
                        self.form.clearErrors();
                        if (result.isConfirmed) {
                            self.form.reset();
                        }
                    });
            }
        },
    };
</script>
