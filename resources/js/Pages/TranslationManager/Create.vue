<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="box mb-6">
            <div class="columns my-0">
                <div class="column py-0">
                    <p class="buttons is-pulled-right">
                        <biz-button
                            v-for="locale in localeOptions"
                            :key="locale.id"
                            :class="['is-small is-link is-rounded', locale.id == selectedLocale ? '' : 'is-light' ]"
                            @click="changeLocale(locale.id)"
                        >
                            {{ locale.name }}
                        </biz-button>
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <translation-manager-form
                    v-model="form"
                    :base-route-name="baseRouteName"
                    :group-options="groupOptions"
                    :is-input-disabled="isProcessing"
                    :selected-locale="selectedLocale"
                    :reference-locale="referenceLocale"
                />

                <div class="field is-grouped is-grouped-right">
                    <div class="control">
                        <biz-button-link
                            :href="route(baseRouteName+'.edit')"
                            class="is-link is-light"
                        >
                            {{ i18n.cancel }}
                        </biz-button-link>
                    </div>
                    <div class="control">
                        <biz-button class="is-link">
                            {{ i18n.create }}
                        </biz-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import TranslationManagerForm from '@/Pages/TranslationManager/Form.vue';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { usePage, useForm } from '@inertiajs/vue3';

    export default {
        name: 'TranslationManagerCreate',

        components: {
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            TranslationManagerForm,
        },

        provide() {
            return {
                i18n: this.i18n,
            };
        },

        layout: AppLayout,

        props: {
            baseRouteName: {type: String, required: true},
            groupOptions: {type: Object, default: () => {}},
            referenceLocale: {type: String, required: true},
            title: {type: String, required: true},
            i18n: {type: Object, default: () => ({
                create : 'Create',
                cancel : 'Cancel',
            })}
        },

        setup() {
            let value = {};
            let localeOptions = usePage().props.languageOptions;

            localeOptions.forEach(function (locale) {
                value[locale.id] = null;
            });

            return {
                defaultLocale: usePage().props.defaultLanguage,
                localeOptions: localeOptions,
                form: useForm({
                    key: null,
                    value: value,
                }),
            };
        },

        data() {
            return {
                isProcessing: false,
                loader: null,
                selectedLocale: this.referenceLocale,
            };
        },

        methods: {
            submit() {
                const self = this;

                self.form.post(route(self.baseRouteName + '.store'), {
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
                    },
                });
            },

            changeLocale(localeId) {
                let locale = {};

                this.localeOptions.map(localeOption => {
                    if (localeOption.id == this.referenceLocale) {
                        locale = localeOption;
                    }
                });

                if (!this.form.value[this.referenceLocale]) {
                    oopsAlert({
                        text: 'Please provide '+locale.name+' ('+locale.id.toUpperCase()+') translation first!',
                    });
                } else {
                    this.selectedLocale = localeId;
                }
            },
        },
    };
</script>
