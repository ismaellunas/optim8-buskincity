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

                <hr>

                <div class="columns">
                    <div class="column">
                        <h2><b>Favicon</b></h2>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <b>Icon</b>
                    </div>

                    <div class="column">
                        <biz-form-media-library
                            v-model="form.favicon"
                            image-preview-size="is-6"
                            :is-download-enabled="can?.media?.read ?? false"
                            :is-upload-enabled="can?.media?.add ?? false"
                            :medium="faviconMedia"
                            :instructions="instructions.mediaLibrary"
                            :message="error('favicon')"
                        />
                    </div>
                </div>

                <hr>

                <div class="columns">
                    <div class="column">
                        <h2><b>QR Code Public Page</b></h2>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <h2><b>Is displayed?</b></h2>
                    </div>
                    <div class="column">
                        <biz-form-select
                            v-model="form.qrcode_public_page_is_displayed"
                            :message="error('qrcode_public_page_is_displayed')"
                        >
                            <option :value="true">
                                Enabled
                            </option>
                            <option :value="false">
                                Disabled
                            </option>
                        </biz-form-select>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <b>Logo</b>
                    </div>

                    <div class="column">
                        <biz-form-media-library
                            v-model="form.qrcode_public_page_logo"
                            image-preview-size="is-6"
                            :is-download-enabled="can?.media?.read ?? false"
                            :is-upload-enabled="can?.media?.add ?? false"
                            :medium="qrCodeMedia"
                            :instructions="instructions.mediaLibrary"
                            :message="error('qrcode_public_page_logo')"
                        />
                    </div>
                </div>

                <hr>

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
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormMediaLibrary from '@/Biz/Form/MediaLibrary.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizTextarea from '@/Biz/Textarea.vue';
    import { assign, mapValues, sortBy } from 'lodash';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'ThemeOptionAdvance',

        components: {
            BizButton,
            BizErrorNotifications,
            BizFormMediaLibrary,
            BizFormSelect,
            BizInputError,
            BizTextarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        layout: AppLayout,

        props: {
            additionalCodes: {type: Object, required: true},
            baseRouteName: {type: String, required: true},
            can: { type: Object, default: () => {} },
            errors: {type: Object, default: () => {}},
            faviconMedia: { type: Object, default: () => {} },
            homePageId: {type: [Number, String, null], default: null},
            instructions: {type: Object, default: () => {}},
            pageOptions: {type: Object, default: () => {}},
            qrCodePublicPageIsDisplayed: {type: Boolean, required: true},
            qrCodeMedia: { type: Object, default: () => {} },
            title: {type: String, required: true},
            trackingCodes: {type: Object, required: true},
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

            const qrCodePublicPageForm = mapValues(
                props.qrCodePublicPages,
                (qrCodePublicPage) => {
                    return qrCodePublicPage.value;
                }
            );

            const qrCodeLogo = {
                qrcode_public_page_is_displayed: props.qrCodePublicPageIsDisplayed,
                qrcode_public_page_logo: props.qrCodeMedia?.id ?? null,
            };

            const homePageForm = { home_page: props.homePageId };

            const favicon = {
                favicon: props.faviconMedia?.id ?? null,
            };

            return {
                form: useForm(assign(
                    additionalCodeForm,
                    favicon,
                    homePageForm,
                    qrCodeLogo,
                    qrCodePublicPageForm,
                    trackingCodeForm,
                )),
                sortedPageOptions: sortBy(usePage().props.value.pageOptions, [(option) => option.value]),
            };
        },

        data() {
            return {
                isProcessing: false,
                loader: null,
                imageTypes: acceptedImageTypes,
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

                        location.reload();
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
