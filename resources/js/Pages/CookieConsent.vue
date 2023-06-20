<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="box mb-6">
            <form @submit.prevent="onSubmit">
                <div class="columns">
                    <div class="column">
                        <h3 class="title is-3">
                            {{ capitalCase(i18n.settings) }}
                        </h3>
                    </div>
                    <div class="column">
                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button class="is-link">
                                    {{ i18n.save }}
                                </biz-button>
                            </div>
                        </div>
                    </div>
                </div>

                <fieldset :disabled="isProcessing">
                    <div class="columns is-multiline">
                        <div class="column is-6">
                            <span class="has-text-weight-bold">
                                {{ i18n.is_enabled }}
                            </span>
                        </div>

                        <div class="column is-6">
                            <biz-select
                                v-model="form.is_enabled"
                            >
                                <option :value="true">
                                    {{ i18n.enabled }}
                                </option>
                                <option :value="false">
                                    {{ i18n.disabled }}
                                </option>
                            </biz-select>
                        </div>

                        <div class="column is-12">
                            <h3 class="title is-3">
                                {{ capitalCase(i18n.message_templates) }}
                            </h3>
                        </div>

                        <div class="column is-6">
                            <span class="has-text-weight-bold">
                                {{ i18n.message }}
                                <sup class="has-text-danger">*</sup>
                            </span>
                        </div>

                        <div class="column is-6">
                            <biz-text-editor
                                v-model="form.message"
                                :config="editorConfig"
                            />

                            <biz-input-error :message="error('message')" />
                        </div>

                        <div class="column is-6">
                            <span class="has-text-weight-bold">
                                {{ i18n.message_decline }}
                                <sup class="has-text-danger">*</sup>
                            </span>
                        </div>

                        <div class="column is-6">
                            <biz-text-editor
                                v-model="form.message_decline"
                                :config="editorConfig"
                            />

                            <biz-input-error :message="error('message_decline')" />
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
    import BizInputError from '@/Biz/InputError.vue';
    import BizSelect from '@/Biz/Select.vue';
    import BizTextEditor from '@/Biz/EditorTinymce.vue';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';
    import { capitalCase } from 'change-case';
    import { defaultConfig } from '@/Libs/tinymce-configs';

    export default {
        name: 'SettingKey',

        components: {
            BizButton,
            BizErrorNotifications,
            BizInputError,
            BizSelect,
            BizTextEditor,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => ({
                save: 'Save',
            }) },
            settings: { type: Object, required: true },
        },

        setup(props) {
            return {
                form: useForm({
                    is_enabled: props.settings?.cookie_consent_is_enabled ?? false,
                    message: props.settings?.cookie_consent_message,
                    message_decline: props.settings?.cookie_consent_message_decline,
                })
            };
        },

        data() {
            return {
                isProcessing: false,
            };
        },

        computed: {
            editorConfig() {
                return defaultConfig;
            },
        },

        methods: {
            onSubmit() {
                const self = this;

                self.form.post(
                    route(self.baseRouteName + '.update'),
                    {
                        onStart: () => {
                            self.isProcessing = true;
                            self.onStartLoadingOverlay();
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                        },
                        onFinish: () => {
                            self.isProcessing = false;
                            self.onEndLoadingOverlay();
                        },
                    }
                )
            },

            capitalCase,
        },
    };
</script>
