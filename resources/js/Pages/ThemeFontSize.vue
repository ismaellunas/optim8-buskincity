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
                <div class="field is-grouped is-grouped-right">
                    <div class="control">
                        <sdb-button class="is-link">
                            Save
                        </sdb-button>
                    </div>
                </div>

                <fieldset :disabled="isProcessing">
                    <div
                        v-for="fontSize in sortedFontSizes"
                        :key="fontSize.key"
                        class="columns"
                    >
                        <div class="column is-three-quarters">
                            <h3>{{ fontSize.display_name }}</h3>
                        </div>
                        <div class="column">
                            <sdb-input
                                v-model="form[fontSize.key]"
                                maxlength="7"
                                @blur="updateFontSizeNumber(fontSize.key)"
                                @keypress="isNumber"
                            />
                            <p v-if="form.errors?.default && form.errors.default[fontSize.key]">
                                <sdb-input-error
                                    :message="error(fontSize.key)"
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
    import SdbInput from '@/Sdb/Input';
    import SdbInputError from '@/Sdb/InputError';
    import { forEach, has, isEmpty, mapValues, sortBy } from 'lodash';
    import { confirm as confirmAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        name: 'ThemeOptionFontSizeEdit',

        components: {
            AppLayout,
            SdbButton,
            SdbErrorNotifications,
            SdbInput,
            SdbInputError,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            baseRouteName: {
                type: String,
                required: true,
            },
            can: {
                type: Object,
                default: () => {}
            },
            fontSizes: {
                type: Object,
                required: true,
            },
            defaultFontSizes: {
                type: Object,
                default: () => {}
            },
            errors: {
                type: Object,
                default: () => {}
            },
            title: {
                type: String,
                required: true,
            },
        },

        setup(props) {
            const fontSizes = mapValues(props.fontSizes, (fontSize) => {
                return fontSize.value;
            });

            const form = mapValues(props.fontSizes, (fontSize, key) => {
                let fontSizeValue = props.fontSizes[key].value;
                if (isEmpty(fontSizeValue) && has(props.defaultFontSizes, key)) {
                    fontSizeValue = props.defaultFontSizes[key];
                }
                return fontSizeValue;
            });

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
            sortedFontSizes() {
                return sortBy(this.fontSizes, ['order']);
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
            updateFontSizeNumber(fontSizeKey) {
                if (! Number.isNaN(parseFloat(this.form[fontSizeKey]))) {
                    this.form[fontSizeKey] = parseFloat(this.form[fontSizeKey]);
                }
            },
            isNumber(event) {
                let keyCode = (event.keyCode ? event.keyCode : event.which);

                if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) {
                    event.preventDefault();
                }
            }
        },
    };
</script>
