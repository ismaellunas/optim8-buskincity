<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <sdb-error-notifications :errors="$page.props.errors" />

        <div class="mb-6">
            <form
                class="columns"
                method="post"
                @submit.prevent="onSubmit"
            >
                <div class="column">
                    <fieldset
                        class="box"
                        :disabled="isProcessing"
                    >
                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <sdb-button class="is-link">
                                    Update
                                </sdb-button>
                            </div>
                        </div>

                        <div class="columns mt-2 is-multiline">
                            <div
                                v-for="option in sortedLanguageOptions"
                                :key="option.id"
                                class="column is-3 py-1"
                            >
                                <div class="field is-horizontal">
                                    <div class="field-body">
                                        <div class="field">
                                            <div class="control">
                                                <sdb-checkbox
                                                    v-model:checked="form.languages"
                                                    :value="option.id"
                                                >
                                                    &nbsp; {{ option.value }}
                                                </sdb-checkbox>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbCheckbox from '@/Sdb/Checkbox';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { sortBy } from 'lodash';

    export default {
        name: 'Language',

        components: {
            AppLayout,
            SdbButton,
            SdbCheckbox,
            SdbErrorNotifications,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            activatedLanguages: {type: Array, required: true},
            baseRouteName: {type: String, required: true},
            errors: {type: Object, default: () => {}},
            languageOptions: {type: Object, required: true},
            title: {type: String, required: true},
        },

        setup(props) {
            const form = {
                languages: props.activatedLanguages,
            };

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
            sortedLanguageOptions() {
                return sortBy(this.languageOptions, ['name']);
            },
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
        },
    };
</script>
