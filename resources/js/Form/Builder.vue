<template>
    <div>
        <div class="columns">
            <div class="column">
                <biz-language-tab
                    class="is-right"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @on-change-locale="onChangeLocale"
                />
            </div>
        </div>

        <biz-error-notifications
            :bags="[bagName]"
            :errors="$page.props.errors"
        />

        <form-field
            v-for="(formField, index) in sortedForms"
            :key="index"
            class="mb-5"
            :entity-id="entityId"
            :form-field="formField"
            :locale-options="localeOptions"
            :selected-locale="selectedLocale"
            :route-name="routeName"
            :route-save="routeSave"
            @on-success-submit="getSchemas()"
        />
    </div>
</template>

<script>
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizLanguageTab from '@/Biz/LanguageTab.vue';
    import FormField from './FormField.vue';
    import { isEmpty, sortBy, find } from 'lodash';
    import { usePage } from '@inertiajs/vue3';
    import { ref } from 'vue';

    export default {
        name: 'FormBuilder',

        components: {
            BizErrorNotifications,
            BizLanguageTab,
            FormField,
        },

        provide() {
            return {
                bagName: this.bagName,
            };
        },

        props: {
            bagName: { type: String, default: 'formBuilder' },
            entityId: {type: Number, default: null},
            locale: { type: String, default: null },
            routeGetSchemas: { type: String, default: 'forms.schemas' },
            routeName: { type: String, required: true },
            routeSave: { type: String, default: 'forms.save' },
        },

        emits: [
            'loaded-empty-field',
            'loaded-forbidden',
            'loaded-successfully'
        ],

        setup(props) {
            const defaultLocale = usePage().props.defaultLanguage;
            let selectedLocale = props.locale ?? defaultLocale;

            const localeOptions = sortBy(
                usePage().props.languageOptions,
                [
                    function(locale) {
                        return locale.id != selectedLocale;
                    }
                ]
            );

            if (typeof find(localeOptions, { 'id': selectedLocale }) === 'undefined') {
                selectedLocale = defaultLocale;
            }

            return {
                localeOptions: localeOptions,
                selectedLocale: ref(selectedLocale),
            };
        },

        data() {
            return {
                forms: {},
                loader: null,
            };
        },

        computed: {
            sortedForms() {
                return sortBy(this.forms, ['order']);
            },
        },

        mounted() {
            this.getSchemas();
        },

        methods: {
            getSchemas() {
                const self = this;

                return axios.get(
                    route(self.routeGetSchemas),
                    {
                        params: {
                            id: self.entityId,
                            route_name: self.routeName
                        }
                    }

                ).then((response) => {
                    self.forms = response.data;

                    self.$emit('loaded-successfully', response.data);

                    if (isEmpty(this.forms)) {
                        self.$emit('loaded-empty-field');
                    }

                }).catch((error) => {
                    if (error.response) {
                        if (error.response.status == 403) {
                            self.$emit('loaded-forbidden', error.response);
                        }
                    }
                });
            },

            onChangeLocale(localeId) {
                this.selectedLocale = localeId;
            }
        },
    };
</script>
