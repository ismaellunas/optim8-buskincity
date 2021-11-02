<template>
    <sdb-modal-card>
        <template v-slot:header>
            <p class="modal-card-title has-text-weight-bold">
                {{ menuItem.id ? 'Edit' : 'Add' }} Menu Item
            </p>
            <button class="delete" aria-label="close" @click="$emit('close')"></button>
        </template>
        <form method="post">
            <fieldset>
                <template
                    v-for="translation in sortedExistingLocales"
                    :key="translation"
                >
                    <sdb-form-input-addons
                        v-model="formLocale[translation].title"
                        :label="`Title (${translation.toUpperCase()})`"
                        :message="error(translation+'.title')"
                        required
                    >
                        <template v-slot:afterInput>
                            <div class="control">
                                <sdb-button-icon
                                    v-if="translation !== defaultLocale"
                                    class="is-danger"
                                    icon="fas fa-minus"
                                    type="button"
                                    @click.prevent="removeTranslation(translation)"
                                />
                            </div>
                        </template>
                    </sdb-form-input-addons>
                </template>
                <div v-if="availableLocales.length">
                    <div class="control is-expanded">
                        <sdb-select v-model="selectedLocale">
                            <option
                                v-for="locale in availableLocales"
                                :key="locale.id"
                                :value="locale.id">
                                {{ locale.name }}
                            </option>
                        </sdb-select>
                        <sdb-button-icon
                            icon="fas fa-plus"
                            type="button"
                            class="is-link is-light"
                            @click.prevent="addTranslation"
                        />
                    </div>
                </div>
                <hr>
                <sdb-form-select
                    v-model="form.type"
                    label="Type"
                    class="is-fullwidth"
                    required
                    :message="error('type')"
                >
                    <template v-for="(type, index) in types" :key="index">
                        <option :value="type">{{ type }}</option>
                    </template>
                </sdb-form-select>
                <sdb-form-input
                    v-if="isTypeUrl"
                    v-model="form.url"
                    label="Url"
                    :message="error('url')"
                ></sdb-form-input>
                <sdb-form-select
                    v-if="isTypePage"
                    v-model="form.page_id"
                    label="Link Page"
                    class="is-fullwidth"
                    :message="error('page_id')"
                >
                    <template v-for="page in pages" :key="page.id">
                        <option :value="page.id">{{ page.title }}</option>
                    </template>
                </sdb-form-select>
                <sdb-form-select
                    v-if="isTypePost"
                    v-model="form.post_id"
                    label="Link Post"
                    class="is-fullwidth"
                    :message="error('post_id')"
                >
                    <template v-for="post in posts" :key="post.id">
                        <option :value="post.id">{{ post.title }}</option>
                    </template>
                </sdb-form-select>
                <sdb-form-select
                    v-if="isTypeCategory"
                    v-model="form.category_id"
                    label="Link Category"
                    class="is-fullwidth"
                    :message="error('category_id')"
                >
                    <template v-for="category in categories" :key="category.id">
                        <option :value="category.id">{{ category.name }}</option>
                    </template>
                </sdb-form-select>
            </fieldset>
        </form>
        <template v-slot:footer>
            <div class="columns" style="width: 100%">
                <div class="column">
                    <div class="is-pulled-right">
                        <sdb-button @click="$emit('close')">Cancel</sdb-button>
                        <sdb-button
                            @click="onSubmit()"
                            class="is-primary ml-1"
                        >
                            {{ menuItem.id ? 'Update' : 'Create' }}
                        </sdb-button>
                    </div>
                </div>
            </div>
        </template>
    </sdb-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormInputAddons from '@/Sdb/Form/InputAddons';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import SdbModalCard from '@/Sdb/ModalCard';
    import SdbSelect from '@/Sdb/Select';
    import { isBlank } from '@/Libs/utils';
    import { pull, sortBy, merge } from 'lodash';
    import { reactive } from "vue";
    import { success as successAlert, confirmDelete } from '@/Libs/alert';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'ModalFormMenu',

        components: {
            SdbButton,
            SdbButtonIcon,
            SdbFormInput,
            SdbFormInputAddons,
            SdbFormSelect,
            SdbModalCard,
            SdbSelect,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            baseRouteName: {
                type: String,
                required: true,
            },
            menu: {
                type: Object,
                required: true,
            },
            menuItem: {
                type: Object,
                default: {},
            },
        },

        emits: [
            'close',
            'syncMenuItems',
        ],

        setup(props) {
            let providedLocales = [];
            let fields = {};
            let fieldLocales = {};

            if (!isBlank(props.menuItem)) {
                providedLocales = props.menuItem.translations.map(translation => {
                    return translation.locale;
                });

                props.menuItem.translations.forEach(translation => {
                    fieldLocales[translation.locale] = {title: translation.title};
                });

                fields = props.menuItem;
            } else {
                providedLocales = ['en'];

                fieldLocales = {
                    en: {title: null},
                };

                fields = {
                    id: null,
                    type: 'Url',
                    url: null,
                    page_id: null,
                    post_id: null,
                    category_id: null,
                    menu_id: props.menu.id,
                };
            }

            return {
                categories: usePage().props.value.categories,
                defaultLocale: usePage().props.value.defaultLanguage,
                form: reactive(fields),
                formLocale: reactive(fieldLocales),
                localeOptions: usePage().props.value.languageOptions,
                pages: usePage().props.value.pages,
                posts: usePage().props.value.posts,
                types: usePage().props.value.types,
                selectedLocale: usePage().props.value.languageOptions.find((localeOption) => {
                    return !providedLocales.includes(localeOption.id);
                })?.id,
            };
        },

        data() {
            return {
                loader: null,
            };
        },

        computed: {
            isTypeUrl() {
                return this.form.type == 'Url';
            },

            isTypePage() {
                return this.form.type == 'Page';
            },

            isTypePost() {
                return this.form.type == 'Post';
            },

            isTypeCategory() {
                return this.form.type == 'Category'
            },

            availableLocales() {
                const usedLocales = Object.keys(this.formLocale);
                return sortBy(this.localeOptions.filter(localeOption => {
                    return !usedLocales.includes(localeOption.id);
                }), ['title']);
            },

            hasAvailableLocales() {
                return !isBlank(this.availableLocales);
            },

            sortedExistingLocales() {
                const sortedExistingLocales = pull(
                    Object.keys(this.formLocale),
                    this.defaultLocale
                );
                sortedExistingLocales.unshift(this.defaultLocale);
                return sortedExistingLocales;
            },
        },

        methods: {
            addTranslation() {
                this.formLocale[this.selectedLocale] = {title: null};

                this.updateSelectedLocale();
            },

            updateSelectedLocale() {
                const usedLocales = Object.keys(this.formLocale);

                if (this.hasAvailableLocales) {
                    const firstAvailabeLocale = this
                        .availableLocales
                        .find((localeOption) => {
                            return !usedLocales.includes(localeOption.id);
                        });

                    this.selectedLocale = firstAvailabeLocale?.id;
                } else {
                    this.selectedLocale = null;
                }
            },

            removeTranslation(locale) {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        delete self.formLocale[locale];
                        self.updateSelectedLocale();
                    }
                });
            },

            onSubmit() {
                const self = this;
                const form = merge(this.form, this.formLocale);

                if (form.id === null) {
                    this.$inertia.post(route(this.baseRouteName+'.store'), form, {
                        preserveState: true,
                        onStart: () => {
                            self.loader = self.$loading.show();
                            self.isProcessing = true;
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                        },
                        onFinish: () => {
                            self.loader.hide();
                            self.isProcessing = false;
                            this.$emit('close');
                            this.$emit('syncMenuItems');
                        }
                    });
                } else {
                    this.$inertia.put(route(this.baseRouteName+'.update', form.id), form, {
                        preserveState: true,
                        onStart: () => {
                            self.loader = self.$loading.show();
                            self.isProcessing = true;
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                        },
                        onFinish: () => {
                            self.loader.hide();
                            self.isProcessing = false;
                            this.$emit('close');
                            this.$emit('syncMenuItems');
                        }
                    });
                }
            },
        },
    }
</script>