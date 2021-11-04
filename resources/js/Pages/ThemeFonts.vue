<template>
    <app-layout>
        <Head>
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
        </Head>

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
                        <h2><b>Typography</b></h2>
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
                    <div class="columns">
                        <div class="column is-half">
                            <h3><b>Uppercase Text</b></h3>
                        </div>
                        <div class="column">
                            <div class="field is-grouped is-grouped-multiline">
                                <p
                                    v-for="(uppercase, key) in uppercaseOptions"
                                    :key="key"
                                    class="control"
                                >
                                    <sdb-checkbox
                                        v-model:checked="form.uppercase_text"
                                        :value="key"
                                    >
                                        &nbsp;{{ uppercase }}
                                    </sdb-checkbox>
                                </p>
                            </div>

                            <p v-if="error('uppercase_text')">
                                <sdb-input-error
                                    :message="error('uppercase_text')"
                                />
                            </p>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-half">
                            <h3><b>Content Paragraph Widht</b></h3>
                        </div>
                        <div class="column">
                            <div class="field has-addons">
                                <p class="control">
                                    <sdb-input
                                        v-model="form.content_paragraph_width"
                                        type="number"
                                    />
                                </p>
                                <p class="control">
                                    <span class="button is-static">px</span>
                                </p>
                            </div>
                            <p v-if="form.errors?.default && form.errors.default['content_paragraph_width']">
                                <sdb-input-error
                                    :message="error('content_paragraph_width')"
                                />
                            </p>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-half">
                            <h3><b>Headings Font</b></h3>
                        </div>
                        <div class="column">
                            <sdb-form-select
                                v-model="form.headings_font_family"
                                label="Font Family"
                                :message="error('headings_font_family')"
                            >
                                <option
                                    v-for="font in fonts"
                                    :key="font.family"
                                    :value="font.family"
                                >
                                    {{ font.family }}
                                </option>
                            </sdb-form-select>

                            <sdb-form-select
                                v-model="form.headings_font_weight"
                                label="Font Weight"
                                :message="error('headings_font_weight')"
                            >
                                <option
                                    v-for="weight, weightClass in weightOptions"
                                    :key="weightClass"
                                    :value="weightClass"
                                >
                                    {{ weight }}
                                </option>
                            </sdb-form-select>

                            <sdb-form-select
                                v-model="form.headings_font_style"
                                label="Font Style"
                                :message="error('headings_font_style')"
                            >
                                <option
                                    v-for="style, styleClass in styleOptions"
                                    :key="styleClass"
                                    :value="styleClass"
                                >
                                    {{ style }}
                                </option>
                            </sdb-form-select>

                            <sdb-label>Preview</sdb-label>
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
                            <h3><b>Main Text Font</b></h3>
                        </div>
                        <div class="column">
                            <sdb-form-select
                                v-model="form.main_text_font_family"
                                label="Font Family"
                                :message="error('main_text_font_family')"
                            >
                                <option
                                    v-for="font in fonts"
                                    :key="font.family"
                                    :value="font.family"
                                >
                                    {{ font.family }}
                                </option>
                            </sdb-form-select>

                            <sdb-form-select
                                v-model="form.main_text_font_weight"
                                label="Font Weight"
                                :message="error('main_text_font_weight')"
                            >
                                <option
                                    v-for="weight, weightClass in weightOptions"
                                    :key="weightClass"
                                    :value="weightClass"
                                >
                                    {{ weight }}
                                </option>
                            </sdb-form-select>

                            <sdb-form-select
                                v-model="form.main_text_font_style"
                                label="Font Style"
                                :message="error('main_text_font_style')"
                            >
                                <option
                                    v-for="style, styleClass in styleOptions"
                                    :key="styleClass"
                                    :value="styleClass"
                                >
                                    {{ style }}
                                </option>
                            </sdb-form-select>

                            <sdb-label>Preview</sdb-label>
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
                            <h3><b>Buttons Font</b></h3>
                        </div>
                        <div class="column">
                            <sdb-form-select
                                v-model="form.buttons_font_family"
                                label="Font Family"
                                :message="error('buttons_font_family')"
                            >
                                <option
                                    v-for="font in fonts"
                                    :key="font.family"
                                    :value="font.family"
                                >
                                    {{ font.family }}
                                </option>
                            </sdb-form-select>

                            <sdb-form-select
                                v-model="form.buttons_font_weight"
                                label="Font Weight"
                                :message="error('buttons_font_weight')"
                            >
                                <option
                                    v-for="weight, weightClass in weightOptions"
                                    :key="weightClass"
                                    :value="weightClass"
                                >
                                    {{ weight }}
                                </option>
                            </sdb-form-select>

                            <sdb-form-select
                                v-model="form.buttons_font_style"
                                label="Font Style"
                                :message="error('buttons_font_style')"
                            >
                                <option
                                    v-for="style, styleClass in styleOptions"
                                    :key="styleClass"
                                    :value="styleClass"
                                >
                                    {{ style }}
                                </option>
                            </sdb-form-select>

                            <sdb-label>Preview</sdb-label>
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
                </fieldset>
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
    import SdbFormSelect from '@/Sdb/Form/Select';
    import SdbInput from '@/Sdb/Input';
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';
    import { Head, useForm } from '@inertiajs/inertia-vue3';
    import { concat, isEmpty, replace } from 'lodash';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        name: 'ThemeOptionFonts',

        components: {
            AppLayout,
            Head,
            SdbButton,
            SdbCheckbox,
            SdbErrorNotifications,
            SdbInput,
            SdbInputError,
            SdbFormSelect,
            SdbLabel,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            baseRouteName: {type: String, default: 'admin.theme.advance'},
            title: {type: String, default: 'Fonts'},
            errors: {type: Object, default: () => {}},
            uppercaseText: {type: Object, default: () => {}},
            contentParagraphWidth: {type: Number, default: 1440},
            headingsFont: {type: Object, default: () => {}},
            mainTextFont: {type: Object, default: () => {}},
            buttonsFont: {type: Object, default: () => {}},
            uppercaseOptions: {type: Object, default: () => {}},
            webfontsUrl: {type: String, default: ''},
            baseUrlGoogleFont: {
                type: String,
                default: "https://fonts.googleapis.com/css2"
            },
        },

        setup(props) {
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
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                isProcessing: false,
                loader: null,
                fonts: [],
                weightOptions: {
                    'has-text-weight-light': "Light",
                    'has-text-weight-normal': "Normal",
                    'has-text-weight-medium': "Medium",
                    'has-text-weight-semibold': "Semibold",
                    'has-text-weight-bold': "Bold",
                },
                styleOptions: {
                    'is-capitalize': "Capitalize",
                    'is-lowercase': "Lowercase",
                    'is-uppercase': "Uppercase",
                    'is-italic': "Italic",
                    'is-underlined': "Underline",
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
        },

        mounted() {
            this.getFonts();
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
                        self.form.isDirty = false;
                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;
                    }
                });
            },
            getFonts() {
                const self = this;

                self.loader = self.$loading.show();

                axios.get(this.webfontsUrl)
                    .then((response) => {
                        self.fonts = response.data.items;
                        self.loader.hide();
                    });
            },
            generateFontUrl(fontFamily) {
                const apiUrl = [ this.baseUrlGoogleFont + '?family=' ];

                apiUrl.push(fontFamily);
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
        },
    };
</script>
