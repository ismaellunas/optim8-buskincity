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
                        <biz-form-input-addons
                            v-model="form[translation].name"
                            :label="`Category Name (${translation.toUpperCase()})`"
                            :message="error(translation+'.name')"
                        >
                            <template #afterInput>
                                <div class="control">
                                    <biz-button-icon
                                        v-if="translation !== defaultLocale"
                                        class="is-danger"
                                        icon="fas fa-minus"
                                        type="button"
                                        @click.prevent="removeTranslation(translation)"
                                    />
                                </div>
                            </template>
                        </biz-form-input-addons>
                    </div>
                </div>
            </div>
            <div class="mb-5" v-if="availableLocales.length">
                <div class="control is-expanded">
                    <biz-select v-model="selectedLocale">
                        <option
                            v-for="locale in availableLocales"
                            :value="locale.id">
                            {{ locale.name }}
                        </option>
                    </biz-select>
                    <biz-button-icon
                        icon="fas fa-plus"
                        type="button"
                        class="is-link is-light"
                        @click.prevent="addTranslation"
                    />
                </div>
            </div>

            <div class="field is-grouped is-grouped-right">
                <div class="control">
                    <biz-button-link :href="route(baseRoute+'.index')" class="is-link is-light">
                        Cancel
                    </biz-button-link>
                </div>
                <div class="control">
                    <biz-button class="is-link">
                        <template v-if="isNew">Create</template>
                        <template v-else>Update</template>
                    </biz-button>
                </div>
            </div>
        </fieldset>
    </form>
</template>

<script>
    import HasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFormInputAddons from '@/Biz/Form/InputAddons';
    import BizLabel from '@/Biz/Label';
    import BizSelect from '@/Biz/Select';
    import { confirmDelete } from '@/Libs/alert';
    import { isBlank } from '@/Libs/utils';
    import { reactive } from "vue";
    import { pull, sortBy, isEmpty } from 'lodash';

    export default {
        name: 'CategoryForm',
        components: {
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizFormInputAddons,
            BizSelect,
        },
        mixins: [
            HasPageErrors
        ],
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

                if (!providedLocales.includes(props.defaultLocale)) {
                    fields[props.defaultLocale] = {name: null};
                    providedLocales.push(props.defaultLocale);
                }
            } else {
                providedLocales = [props.defaultLocale];

                fields[props.defaultLocale] = {
                    name: null
                };
            }

            return {
                form: reactive(fields),
                selectedLocale: props.localeOptions.find((localeOption) => {
                    return !providedLocales.includes(localeOption.id);
                })?.id,
            };
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
                this.form[this.selectedLocale] = {name: null,};

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
    };
</script>
