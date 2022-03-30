<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

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
                            Cancel
                        </biz-button-link>
                    </div>
                    <div class="control">
                        <biz-button class="is-link">
                            Create
                        </biz-button>
                    </div>
                </div>
            </form>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import TranslationManagerForm from '@/Pages/TranslationManager/Form';
    import { oops as oopsAlert } from '@/Libs/alert';
    import { usePage, useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            TranslationManagerForm,
        },

        props: {
            baseRouteName: {type: String, required: true},
            groupOptions: {type: Object, default: () => {}},
            referenceLocale: {type: String, required: true},
            title: {type: String, required: true},
        },

        setup() {
            let value = {};
            let localeOptions = usePage().props.value.languageOptions;

            localeOptions.forEach(function (locale) {
                value[locale.id] = null;
            });

            return {
                defaultLocale: usePage().props.value.defaultLanguage,
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
