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
                        <biz-image
                            v-if="hasFavicon"
                            class="mb-2"
                            style="width: 200px; border: 1px solid #000"
                            :src="faviconImageUrl"
                        />
                        <biz-form-file
                            v-model="form.favicon"
                            :accepted-types="imageTypes"
                            :is-name-displayed="false"
                            :message="error('favicon')"
                            :notes="instructions.favicon"
                            @on-file-picked="onFilePickedFavicon"
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
                        <biz-image
                            v-if="hasQrCodeLogo"
                            class="mb-2"
                            style="width: 200px; border: 1px solid #000"
                            :src="qrCodeLogoUrl"
                        />
                        <biz-form-file
                            v-model="form.qrcode_public_page_logo"
                            :accepted-types="imageTypes"
                            :is-name-displayed="false"
                            :message="error('qrcode_public_page_logo')"
                            :notes="instructions.qrcode"
                            @on-file-picked="onFilePickedQrCode"
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
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFormFile from '@/Biz/Form/File';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizImage from '@/Biz/Image';
    import BizInputError from '@/Biz/InputError';
    import BizTextarea from '@/Biz/Textarea';
    import { assign, mapValues, sortBy, isEmpty } from 'lodash';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'ThemeOptionAdvance',

        components: {
            AppLayout,
            BizButton,
            BizErrorNotifications,
            BizFormFile,
            BizFormSelect,
            BizImage,
            BizInputError,
            BizTextarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            additionalCodes: {type: Object, required: true},
            baseRouteName: {type: String, required: true},
            errors: {type: Object, default: () => {}},
            faviconUrl: {type: String, default: ""},
            homePageId: {type: [Number, String, null], default: null},
            instructions: {type: Object, default: () => {}},
            pageOptions: {type: Object, default: () => {}},
            qrCodePublicPageIsDisplayed: {type: Boolean, required: true},
            qrCodePublicPageLogo: {type: String, required: true},
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
                qrcode_public_page_logo: null
            };

            const homePageForm = { home_page: props.homePageId };

            const favicon = { favicon: null };

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
                qrCodeLogoUrl: this.qrCodePublicPageLogo,
                faviconImageUrl: this.faviconUrl,
                imageTypes: acceptedImageTypes,
            };
        },

        computed: {
            hasQrCodeLogo() {
                return !isEmpty(this.qrCodeLogoUrl);
            },

            hasFavicon() {
                return !isEmpty(this.faviconImageUrl);
            },

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

                        self.form.qrcode_public_page_logo = null;
                        self.qrCodeLogoUrl = self.qrCodePublicPageLogo;

                        self.form.favicon = null;
                        self.faviconImageUrl = self.faviconUrl;
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;

                        location.reload();
                    }
                });
            },

            onFilePickedQrCode(event) {
                this.qrCodeLogoUrl = event.target.result;
            },

            onFilePickedFavicon(event) {
                this.faviconImageUrl = event.target.result;
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
