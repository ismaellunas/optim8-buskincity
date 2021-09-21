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
                    <div class="column is-half" v-for="(translation, index) in form">
                        <div class="columns">
                            <div class="column">
                                <sdb-form-input
                                    v-model="form[index].name"
                                    placeholder=""
                                    :label="`Category Name (${index.toUpperCase()})`"
                                    :message="error('form.en.name')"
                                    :required="index === defaultLocale"
                                />
                            </div>
                            <div class="column is-one-fifth">
                                <div class="field" v-if="index !== defaultLocale">
                                    <sdb-label>&nbsp;</sdb-label>
                                    <div class="control">
                                        <sdb-button
                                            class="is-danger"
                                            @click.prevent="removeTranslation(index)">
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
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { reactive } from "vue";

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
        setup(props, { emit }) {
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

                const firstAvailabeLocale = this.localeOptions.find(localeOption => {
                    return !usedLocales.includes(localeOption.id);
                });

                this.selectedLocale = firstAvailabeLocale?.id;
            },
            addTranslation() {
                this.form[this.selectedLocale] = {name: null};

                this.updateSelectedLocale();
            },
            removeTranslation(locale) {
                if (confirm('Are you sure?')) {
                    delete this.form[locale];

                    this.updateSelectedLocale();
                }
            },
        },
        computed: {
            availableLocales() {
                const usedLocales = Object.keys(this.form);
                return this.localeOptions.filter(localeOption => {
                    return !usedLocales.includes(localeOption.id);
                });
            },
            hasAvailableLocales() {
                return !isBlank(this.availableLocales);
            }
        },
    };
</script>
