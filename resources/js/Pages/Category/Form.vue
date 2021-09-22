<template>
    <form
        method="post"
        @submit.prevent="$emit('on-submit', form)"
    >
        <p class="title is-3">Form Category</p>
        <hr>

        <fieldset :disabled="isInputDisabled">
            <div class="mb-5">
                <div class="columns is-multiline">
                    <div
                        v-for="translation in sortedExistingLocales"
                        :key="translation"
                        class="column is-half"
                    >
                        <div class="columns">
                            <div class="column">
                                <sdb-form-input
                                    v-model="form[translation].name"
                                    placeholder=""
                                    :label="`Category Name (${translation.toUpperCase()})`"
                                    :message="error('form.en.name')"
                                    :required="translation === defaultLocale"
                                />
                            </div>
                            <div class="column is-one-fifth">
                                <div class="field" v-if="translation !== defaultLocale">
                                    <sdb-label>&nbsp;</sdb-label>
                                    <div class="control">
                                        <sdb-button
                                            class="is-danger"
                                            @click.prevent="removeTranslation(translation)">
                                            <span class="icon">
                                                <i class="fas fa-minus"></i>
                                            </span>
                                        </sdb-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-5" v-if="availableLocales.length">
                <div class="control is-expanded">
                    <sdb-select v-model="selectedLocale">
                        <option
                            v-for="locale in availableLocales"
                            :value="locale.id">
                            {{ locale.name }}
                        </option>
                    </sdb-select>
                    <sdb-button @click.prevent="addTranslation" class="is-link is-light">
                        <span class="icon">
                            <i class="fas fa-plus"></i>
                        </span>
                    </sdb-button>
                </div>
                <div class="control">
                </div>
            </div>

            <div class="field is-grouped is-grouped-right">
                <div class="control">
                    <sdb-button-link :href="route(baseRoute+'.index')" class="is-link is-light">
                        Cancel
                    </sdb-button-link>
                </div>
                <div class="control">
                    <sdb-button class="is-link">
                        <template v-if="isNew">Create</template>
                        <template v-else>Update</template>
                    </sdb-button>
                </div>
            </div>
        </fieldset>
    </form>
</template>

<script>
    import HasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbLabel from '@/Sdb/Label';
    import SdbSelect from '@/Sdb/Select';
    import { confirmDelete } from '@/Libs/alert';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { reactive } from "vue";
    import { pull, sortBy } from 'lodash';

    export default {
        name: 'CategoryForm',
        mixins: [
            HasPageErrors
        ],
        components: {
            SdbButton,
            SdbLabel,
            SdbButtonLink,
            SdbFormInput,
            SdbSelect,
        },
        props: {
            baseRoute: String,
            category: Object,
            defaultLocale: String,
            isInputDisabled: {type: Boolean, default: false},
            errors: {},
            isEditMode: Boolean,
            isNew: Boolean,
            localeOptions: Array,
        },
        emits: [
           'on-submit',
        ],
        setup(props) {
            let providedLocales = [];
            let fields = {};

            if (!isBlank(props.category)) {
                providedLocales = props.category.translations.map(translation => {
                    return translation.locale;
                });

                props.category.translations.forEach(translation => {
                    fields[translation.locale] = {name: translation.name};
                });
            } else {
                providedLocales = ['en'];

                fields = { en: { name: null } };
            }

            return {
                form: reactive(fields),
                selectedLocale: props.localeOptions.find((localeOption) => {
                    return !providedLocales.includes(localeOption.id);
                })?.id,
            };
        },
        methods: {
            updateSelectedLocale() {
                const usedLocales = Object.keys(this.form);

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
            addTranslation() {
                this.form[this.selectedLocale] = {name: null};

                this.updateSelectedLocale();
            },
            removeTranslation(locale) {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        delete self.form[locale];
                        self.updateSelectedLocale();
                    }
                });
            },
        },
        computed: {
            availableLocales() {
                const usedLocales = Object.keys(this.form);
                return sortBy(this.localeOptions.filter(localeOption => {
                    return !usedLocales.includes(localeOption.id);
                }), ['name']);
            },
            hasAvailableLocales() {
                return !isBlank(this.availableLocales);
            },
            sortedExistingLocales() {
                const sortedExistingLocales = pull(
                    Object.keys(this.form),
                    this.defaultLocale
                );
                sortedExistingLocales.unshift(this.defaultLocale);
                return sortedExistingLocales;
            }
        },
    };
</script>
