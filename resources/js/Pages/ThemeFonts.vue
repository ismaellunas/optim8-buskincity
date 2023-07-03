<template>
    <div>
        <inertia-head>
            <link
                rel="preconnect"
                href="https://fonts.googleapis.com"
            >
            <link
                rel="preconnect"
                href="https://fonts.gstatic.com"
                crossorigin
            >
            <link
                v-if="headingsFontHref"
                rel="stylesheet"
                :href="headingsFontHref"
            >
            <link
                v-if="mainTextFontHref"
                rel="stylesheet"
                :href="mainTextFontHref"
            >
            <link
                v-if="buttonsFontHref"
                rel="stylesheet"
                :href="buttonsFontHref"
            >
        </inertia-head>

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
                                    {{ i18n.save }}
                                </biz-button>
                            </div>
                        </div>
                    </div>
                </div>

                <fieldset :disabled="isProcessing">
                    <div class="columns">
                        <div class="column">
                            <h2 class="title is-2">
                                <b>{{ i18n.typography }}</b>
                            </h2>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-half">
                            <h3>
                                <b>{{ i18n.uppercase_text }}</b>
                            </h3>
                        </div>
                        <div class="column">
                            <div class="field is-grouped is-grouped-multiline">
                                <p
                                    v-for="(uppercase, key) in uppercaseOptions"
                                    :key="key"
                                    class="control"
                                >
                                    <biz-checkbox
                                        v-model:checked="form.uppercase_text"
                                        :value="key"
                                    >
                                        &nbsp;{{ uppercase }}
                                    </biz-checkbox>
                                </p>
                            </div>

                            <p v-if="error('uppercase_text')">
                                <biz-input-error
                                    :message="error('uppercase_text')"
                                />
                            </p>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-half">
                            <h3>
                                <b>{{ i18n.content_paragraph_width }}</b>
                            </h3>
                        </div>
                        <div class="column">
                            <div class="field has-addons">
                                <p class="control">
                                    <biz-input
                                        v-model="form.content_paragraph_width"
                                        type="number"
                                    />
                                </p>
                                <p class="control">
                                    <span class="button is-static">px</span>
                                </p>
                            </div>
                            <p v-if="form.errors?.default && form.errors.default['content_paragraph_width']">
                                <biz-input-error
                                    :message="error('content_paragraph_width')"
                                />
                            </p>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-half">
                            <h3>
                                <b>{{ i18n.heading_font }}</b>
                            </h3>
                        </div>
                        <div class="column">
                            <biz-form-dropdown-search
                                :label="i18n.font_family"
                                :close-on-click="true"
                                @search="searchFont($event, 'headings_font_family')"
                            >
                                <template #trigger>
                                    <span :style="{'min-width': '4rem'}">
                                        {{ form.headings_font_family }}
                                    </span>
                                </template>

                                <biz-dropdown-item @click="form.headings_font_family = null">
                                    (Default)
                                </biz-dropdown-item>

                                <biz-dropdown-item
                                    v-for="(font, index) in filteredFonts.headings_font_family"
                                    :key="index"
                                    @click="form.headings_font_family = font.family"
                                >
                                    {{ font.family }}
                                </biz-dropdown-item>
                            </biz-form-dropdown-search>

                            <biz-form-select
                                v-model="form.headings_font_weight"
                                :label="i18n.font_weight"
                                :message="error('headings_font_weight')"
                            >
                                <option
                                    v-for="weight, weightClass in weightOptions"
                                    :key="weightClass"
                                    :value="weightClass"
                                >
                                    {{ weight }}
                                </option>
                            </biz-form-select>

                            <biz-form-select
                                v-model="form.headings_font_style"
                                :label="i18n.font_style"
                                :message="error('headings_font_style')"
                            >
                                <option
                                    v-for="style, styleClass in styleOptions"
                                    :key="styleClass"
                                    :value="styleClass"
                                >
                                    {{ style }}
                                </option>
                            </biz-form-select>

                            <biz-label>
                                {{ i18n.preview }}
                            </biz-label>
                            <div
                                id="preview-headings"
                                class="box"
                                :class="previewClasses(form.headings_font_weight, form.headings_font_style)"
                                :style="previewStyles(form.headings_font_family)"
                            >
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce interdum, erat condimentum euismod tristique, dui nibh placerat arcu, sit amet lobortis urna quam non est.
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-half">
                            <h3>
                                <b>{{ i18n.main_text_font }}</b>
                            </h3>
                        </div>
                        <div class="column">
                            <biz-form-dropdown-search
                                :label="i18n.font_family"
                                :close-on-click="true"
                                @search="searchFont($event, 'main_text_font_family')"
                            >
                                <template #trigger>
                                    <span :style="{'min-width': '4rem'}">
                                        {{ form.main_text_font_family }}
                                    </span>
                                </template>

                                <biz-dropdown-item @click="form.main_text_font_family = null">
                                    (Default)
                                </biz-dropdown-item>

                                <biz-dropdown-item
                                    v-for="(font, index) in filteredFonts.main_text_font_family"
                                    :key="index"
                                    @click="form.main_text_font_family = font.family"
                                >
                                    {{ font.family }}
                                </biz-dropdown-item>
                            </biz-form-dropdown-search>

                            <biz-form-select
                                v-model="form.main_text_font_weight"
                                :label="i18n.font_weight"
                                :message="error('main_text_font_weight')"
                            >
                                <option
                                    v-for="weight, weightClass in weightOptions"
                                    :key="weightClass"
                                    :value="weightClass"
                                >
                                    {{ weight }}
                                </option>
                            </biz-form-select>

                            <biz-form-select
                                v-model="form.main_text_font_style"
                                :label="i18n.font_style"
                                :message="error('main_text_font_style')"
                            >
                                <option
                                    v-for="style, styleClass in styleOptions"
                                    :key="styleClass"
                                    :value="styleClass"
                                >
                                    {{ style }}
                                </option>
                            </biz-form-select>

                            <biz-label>
                                {{ i18n.preview }}
                            </biz-label>
                            <div
                                id="preview-main-text"
                                class="box"
                                :class="previewClasses(form.main_text_font_weight, form.main_text_font_style)"
                                :style="previewStyles(form.main_text_font_family)"
                            >
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce interdum, erat condimentum euismod tristique, dui nibh placerat arcu, sit amet lobortis urna quam non est.
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-half">
                            <h3>
                                <b>{{ i18n.button_font }}</b>
                            </h3>
                        </div>
                        <div class="column">
                            <biz-form-dropdown-search
                                :label="i18n.font_family"
                                :close-on-click="true"
                                @search="searchFont($event, 'buttons_font_family')"
                            >
                                <template #trigger>
                                    <span :style="{'min-width': '4rem'}">
                                        {{ form.buttons_font_family }}
                                    </span>
                                </template>

                                <biz-dropdown-item @click="form.buttons_font_family = null">
                                    (Default)
                                </biz-dropdown-item>

                                <biz-dropdown-item
                                    v-for="(font, index) in filteredFonts.buttons_font_family"
                                    :key="index"
                                    @click="form.buttons_font_family = font.family"
                                >
                                    {{ font.family }}
                                </biz-dropdown-item>
                            </biz-form-dropdown-search>

                            <biz-form-select
                                v-model="form.buttons_font_weight"
                                :label="i18n.font_weight"
                                :message="error('buttons_font_weight')"
                            >
                                <option
                                    v-for="weight, weightClass in weightOptions"
                                    :key="weightClass"
                                    :value="weightClass"
                                >
                                    {{ weight }}
                                </option>
                            </biz-form-select>

                            <biz-form-select
                                v-model="form.buttons_font_style"
                                :label="i18n.font_style"
                                :message="error('buttons_font_style')"
                            >
                                <option
                                    v-for="style, styleClass in styleOptions"
                                    :key="styleClass"
                                    :value="styleClass"
                                >
                                    {{ style }}
                                </option>
                            </biz-form-select>

                            <biz-label>
                                {{ i18n.preview }}
                            </biz-label>
                            <p
                                id="preview-buttons"
                                class="box buttons"
                            >
                                <button
                                    v-for="buttonClass, index in ['', 'is-primary', 'is-link', 'is-info', 'is-success', 'is-warning', 'is-danger']"
                                    :key="index"
                                    class="button"
                                    type="button"
                                    :class="previewClasses(form.buttons_font_weight, form.buttons_font_style, buttonClass)"
                                    :style="previewStyles(form.buttons_font_family)"
                                >
                                    Normal
                                </button>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="columns">
                        <div class="column is-half">
                            <h2 class="title is-2">
                                <b>{{ i18n.sizes }}</b>
                            </h2>
                        </div>

                        <div class="column">
                            <div class="columns">
                                <div
                                    v-for="(deviceIcon, deviceMode) in devices"
                                    :key="deviceMode"
                                    class="column"
                                >
                                    <biz-icon
                                        :icon="deviceIcon"
                                        :title="deviceMode + ` mode`"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-for="fontSize in sortedFontSizes"
                        :key="fontSize.key"
                        class="columns"
                    >
                        <div class="column is-half">
                            <h3>{{ fontSize.display_name }}</h3>
                        </div>

                        <div class="column">
                            <div class="columns">
                                <div
                                    v-for="(deviceIcon, deviceMode) in devices"
                                    :key="deviceMode"
                                    class="column"
                                >
                                    <div class="field has-addons has-addons-right">
                                        <p class="control">
                                            <biz-input
                                                v-model="form.sizes[fontSize.key][deviceMode]"
                                                maxlength="7"
                                                @blur="updateFontSizeNumber(fontSize.key, deviceMode)"
                                                @keypress="isNumber"
                                            />
                                        </p>
                                        <p class="control">
                                            <button
                                                class="button"
                                                tabindex="-1"
                                                type="button"
                                            >
                                                px
                                            </button>
                                        </p>
                                    </div>

                                    <p v-if="form.errors?.default && form.errors.default[`sizes.` + fontSize.key + `.` + deviceMode]">
                                        <biz-input-error
                                            :message="error(`sizes.` + fontSize.key + `.` + deviceMode)"
                                        />
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizInput from '@/Biz/Input.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizLabel from '@/Biz/Label.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import { Head as InertiaHead, useForm } from '@inertiajs/vue3';
    import { concat, debounce, filter, isEmpty, has, replace, mapValues, sortBy, forEach } from 'lodash';
    import { success as successAlert } from '@/Libs/alert';
    import { debounceTime } from '@/Libs/defaults';
    import { desktop as desktopIcon, tablet as tabletIcon, mobile as mobileIcon } from '@/Libs/icon-class';

    export default {
        name: 'ThemeOptionFonts',

        components: {
            InertiaHead,
            BizButton,
            BizCheckbox,
            BizDropdownItem,
            BizErrorNotifications,
            BizFormDropdownSearch,
            BizFormSelect,
            BizInput,
            BizInputError,
            BizLabel,
            BizIcon,
        },

        mixins: [
            MixinHasPageErrors,
            MixinHasLoader,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: {type: String, required: true},
            baseUrlGoogleFont: {type: String, required: true},
            buttonsFont: {type: Object, required: true},
            contentParagraphWidth: {type: Number, required: true},
            defaultFontSizes: {type: Object, default: () => {}},
            errors: {type: Object, default: () => {}},
            fontSizes: {type: Object, required: true},
            headingsFont: {type: Object, required: true},
            mainTextFont: {type: Object, required: true},
            title: {type: String, required: true},
            uppercaseOptions: {type: Object, required: true},
            uppercaseText: {type: Object, required: true},
            webfontsUrl: {type: String, required: true},
            i18n: { type: Object, default: () => ({
                typography: 'Typography',
                save: 'Save',
                uppercase_text : 'Uppercase text',
                content_paragraph_width : 'Content paragraph width',
                heading_font : 'Heading font',
                font_family : 'Font family',
                font_weight : 'Font weight',
                font_style : 'Font style',
                preview : 'Preview',
                main_text_font : 'Main text font',
                button_font : 'Buttons font',
            }) }
        },

        setup(props) {
            const devices = {
                desktop: desktopIcon,
                tablet: tabletIcon,
                mobile: mobileIcon,
            };

            const fontSizes = mapValues(props.fontSizes, (fontSize, key) => {
                let fontSizeValue = props.fontSizes[key].value;

                forEach(devices, function (device, deviceKey) {

                    if (! fontSizeValue[deviceKey] && has(props.defaultFontSizes, key)) {
                        fontSizeValue[deviceKey] = props.defaultFontSizes[key][deviceKey];
                    }

                });

                return fontSizeValue;
            });

            const form = {
                buttons_font_family: props.buttonsFont.family,
                buttons_font_style: props.buttonsFont.style,
                buttons_font_weight: props.buttonsFont.weight,
                content_paragraph_width: props.contentParagraphWidth,
                headings_font_family: props.headingsFont.family,
                headings_font_style: props.headingsFont.style,
                headings_font_weight: props.headingsFont.weight,
                main_text_font_family: props.mainTextFont.family,
                main_text_font_style: props.mainTextFont.style,
                main_text_font_weight: props.mainTextFont.weight,
                uppercase_text: props.uppercaseText,
                sizes: fontSizes,
            };

            return {
                form: useForm(form),
                devices,
            };
        },

        data() {
            return {
                isProcessing: false,
                fonts: [],
                weightOptions: {
                    'has-text-weight-light': "Light",
                    'has-text-weight-normal': "Normal",
                    'has-text-weight-medium': "Medium",
                    'has-text-weight-semibold': "Semibold",
                    'has-text-weight-bold': "Bold",
                },
                styleOptions: {
                    '': 'Default',
                    'is-capitalized': "Capitalize",
                    'is-lowercase': "Lowercase",
                    'is-uppercase': "Uppercase",
                    'is-italic': "Italic",
                    'is-underlined': "Underline",
                },
                filteredFonts: {
                    'headings_font_family': [],
                    'main_text_font_family': [],
                    'buttons_font_family': [],
                },
            };
        },

        computed: {
            headingsFontHref() {
                if (!isEmpty(this.form.headings_font_family)) {
                    return this.generateFontUrl(this.form.headings_font_family);
                }
                return null;
            },

            mainTextFontHref() {
                if (!isEmpty(this.form.main_text_font_family)) {
                    return this.generateFontUrl(this.form.main_text_font_family);
                }
                return null;
            },

            buttonsFontHref() {
                if (!isEmpty(this.form.buttons_font_family)) {
                    return this.generateFontUrl(this.form.buttons_font_family);
                }
                return null;
            },

            sortedFontSizes() {
                return sortBy(this.fontSizes, ['order']);
            },
        },

        mounted() {
            const self = this;

            this.getFonts().then(function () {
                let initFilteredFonts = self.fonts.slice(0, 10);
                self.filteredFonts.headings_font_family = initFilteredFonts;
                self.filteredFonts.main_text_font_family = initFilteredFonts;
                self.filteredFonts.buttons_font_family = initFilteredFonts;
            });
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
                        self.form.isDirty = false;

                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        self.onEndLoadingOverlay();

                        self.isProcessing = false;
                    }
                });
            },

            getFonts() {
                const self = this;

                self.onStartLoadingOverlay();

                return axios.get(this.webfontsUrl)
                    .then((response) => {
                        self.fonts = response.data.items;

                        self.onEndLoadingOverlay();
                    });
            },

            generateFontUrl(fontFamily) {
                const apiUrl = [ this.baseUrlGoogleFont + '?family=' ];

                apiUrl.push(replace(fontFamily, / /g, '+'));
                apiUrl.push(':ital');
                apiUrl.push(',wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700');
                apiUrl.push('&display=swap');

                return apiUrl.join('');
            },

            previewStyles(fontFamily) {
                let styles = {};

                if (fontFamily) {
                    styles.fontFamily = `"${fontFamily}", sans-serif`;
                }

                return styles;
            },

            previewClasses(fontWeight, fontStyle, additionalClasses) {
                return concat(
                    [],
                    fontWeight,
                    fontStyle,
                    additionalClasses
                ).filter(Boolean);
            },

            searchFont: debounce(function(term, key) {
                if (!isEmpty(term) && term.length > 2) {
                    this.filteredFonts[ key ] = filter(this.fonts, function (font) {
                        return new RegExp(term, 'i').test(font.family);
                    }).slice(0, 10);
                } else {
                    this.filteredFonts[ key ] = this.fonts.slice(0, 10);
                }
            }, debounceTime),

            updateFontSizeNumber(fontSizeKey, device) {
                if (! Number.isNaN(parseFloat(this.form.sizes[fontSizeKey][device]))) {
                    this.form.sizes[fontSizeKey][device] = parseFloat(this.form.sizes[fontSizeKey][device]);
                }
            },

            isNumber(event) {
                let keyCode = (event.keyCode ? event.keyCode : event.which);

                if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) {
                    event.preventDefault();
                }
            },
        },
    };
</script>
