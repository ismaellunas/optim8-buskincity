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
                <div class="columns">
                    <div class="column">
                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button class="is-link">
                                    Save
                                </biz-button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <h2><b>Home Page</b></h2>
                    </div>
                    <div class="column">
                        <biz-form-select
                            v-model="form.home_page"
                            class="is-fullwidth"
                            :message="error(form.home_page, null, errors)"
                        >
                            <option
                                v-for="option in sortedPageOptions"
                                :key="option.id"
                                :value="option.id"
                            >
                                {{ option.value }}
                                <span
                                    v-for="locale, index in option.locales"
                                    :key="index"
                                >
                                    [{{ locale.toUpperCase() }}]
                                </span>
                            </option>
                        </biz-form-select>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <h2><b>Additional Code</b></h2>
                    </div>
                </div>

                <fieldset :disabled="isProcessing">
                    <div
                        v-for="trackingCode in sortedTrackingCodes"
                        :key="trackingCode.key"
                        class="columns"
                    >
                        <div class="column is-half">
                            <h3><b>{{ trackingCode.display_name }}</b></h3>
                        </div>
                        <div class="column">
                            <biz-textarea
                                v-model="form[ trackingCode.key ]"
                                class="code-editor"
                                rows="10"
                            />
                            <p v-if="false">
                                <biz-input-error
                                    :message="error(trackingCode.key)"
                                />
                            </p>
                        </div>
                    </div>

                    <div
                        v-for="additionalCode in sortedAdditionalCodes"
                        :key="additionalCode.key"
                        class="columns"
                    >
                        <div class="column is-half">
                            <h3><b>{{ additionalCode.display_name }}</b></h3>
                        </div>
                        <div class="column">
                            <biz-textarea
                                v-model="form[ additionalCode.key ]"
                                class="code-editor"
                                rows="10"
                            />
                            <p v-if="false">
                                <biz-input-error
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
    import BizFormSelect from '@/Biz/Form/Select';
    import BizButton from '@/Biz/Button';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizTextarea from '@/Biz/Textarea';
    import BizInputError from '@/Biz/InputError';
    import { assign, mapValues, sortBy } from 'lodash';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'ThemeOptionAdvance',

        components: {
            AppLayout,
            BizFormSelect,
            BizButton,
            BizErrorNotifications,
            BizInputError,
            BizTextarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            baseRouteName: {type: String, required: true},
            additionalCodes: {type: Object, required: true},
            trackingCodes: {type: Object, required: true},
            errors: {type: Object, default: () => {}},
            title: {type: String, required: true},
            pageOptions: {type: Object, default: () => {}},
            homePageId: {type: [Number, String, null], default: null},
        },

        setup(props) {
            const additionalCodeForm = mapValues(
                props.additionalCodes,
                (additionalCode) => {
                    return additionalCode.value;
                }
            );

            const trackingCodeForm = mapValues(
                props.trackingCodes,
                (trackingCode) => {
                    return trackingCode.value;
                }
            );

            const homePageForm = { home_page: props.homePageId }

            return {
                form: useForm(assign(
                    additionalCodeForm,
                    trackingCodeForm,
                    homePageForm,
                )),
                sortedPageOptions: sortBy(usePage().props.value.pageOptions, [(option) => option.value]),
            };
        },

        data() {
            return {
                isProcessing: false,
                loader: null,
            };
        },

        computed: {
            sortedAdditionalCodes() {
                return sortBy(this.additionalCodes, ['order']);
            },
            sortedTrackingCodes() {
                return sortBy(this.trackingCodes, ['order']);
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

<style scoped>
.code-editor {
    background-color: black;
    color: #e5e5e5;
    font-family: "Courier New";
    font-size: 10pt;
}
</style>
