<template>
    <div>
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
                            {{ i18n.save }}
                        </biz-button>
                    </div>
                </div>

                <fieldset :disabled="isProcessing">
                    <div
                        v-for="color in sortedColors"
                        :key="color.key"
                        class="columns"
                    >
                        <div class="column is-three-quarters">
                            <h3>{{ color.display_name }}</h3>
                        </div>
                        <div class="column">
                            <biz-input-color
                                v-model="form[color.key]"
                            />
                            <p v-if="form.errors?.default && form.errors.default[color.key]">
                                <biz-input-error
                                    :message="error(color.key)"
                                />
                            </p>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizInputColor from '@/Biz/InputColor.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import { has, isEmpty, mapValues, sortBy } from 'lodash';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';

    export default {
        name: 'ThemeOptionColorEdit',

        components: {
            BizButton,
            BizErrorNotifications,
            BizInputColor,
            BizInputError,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: {
                type: String,
                required: true,
            },
            can: {
                type: Object,
                default: () => {}
            },
            colors: {
                type: Object,
                required: true,
            },
            defaultColors: {
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
            i18n: {
                type: Object,
                default: () => ({
                    save: 'Save',
                })
            }
        },

        setup(props) {
            const colors = mapValues(props.colors, (color) => {
                return color.value;
            });

            const form = mapValues(props.colors, (color, key) => {
                let colorValue = props.colors[key].value;
                if (isEmpty(colorValue) && has(props.defaultColors, key)) {
                    colorValue = props.defaultColors[key];
                }
                return colorValue;
            });

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                isProcessing: false,
            };
        },

        computed: {
            sortedColors() {
                return sortBy(this.colors, ['order']);
            },
        },

        methods: {
            onSubmit() {
                const self = this;
                this.form.post(route(this.baseRouteName+'.update'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.onStartLoadingOverlay();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.form.isDirty = false;
                    },
                    onFinish: () => {
                        self.onEndLoadingOverlay();
                        self.isProcessing = false;
                    }
                });
            },
        },
    };
</script>
