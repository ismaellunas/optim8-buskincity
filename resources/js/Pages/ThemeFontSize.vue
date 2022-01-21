<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <div class="box mb-6">
            <form
                method="post"
                @submit.prevent="onSubmit"
            >
                <div class="field is-grouped is-grouped-right">
                    <div class="control">
                        <biz-button class="is-link">
                            Save
                        </biz-button>
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
                            <biz-input
                                v-model="form[fontSize.key]"
                                maxlength="7"
                                @blur="updateFontSizeNumber(fontSize.key)"
                                @keypress="isNumber"
                            />
                            <p v-if="form.errors?.default && form.errors.default[fontSize.key]">
                                <biz-input-error
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
    import BizButton from '@/Biz/Button';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizInput from '@/Biz/Input';
    import BizInputError from '@/Biz/InputError';
    import { forEach, has, isEmpty, mapValues, sortBy } from 'lodash';
    import { confirm as confirmAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        name: 'ThemeOptionFontSizeEdit',

        components: {
            AppLayout,
            BizButton,
            BizErrorNotifications,
            BizInput,
            BizInputError,
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
