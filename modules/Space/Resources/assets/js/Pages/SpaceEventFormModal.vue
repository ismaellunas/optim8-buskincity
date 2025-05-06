<template>
    <biz-modal-card
        content-class="is-huge"
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ capitalCase(isNew ? i18n.add_new_event : i18n.edit_event) }}
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="$emit('close')"
            />
        </template>

        <form
            v-if="!isBlank(form)"
            @submit.prevent="submit"
        >
            <biz-form-input
                v-model="form.title"
                :label="i18n.title"
                maxlength="255"
                required
                :message="error('title', null, formErrors)"
            />

            <div class="columns">
                <div class="column is-half">
                    <biz-form-date-time
                        v-model="startedEndedAt"
                        :label="i18n.started_and_ended_at"
                        required
                        multi-calendars
                        multi-calendars-solo
                        range
                        :utc="'preserve'"
                        :message="error(['started_at', 'ended_at'], null, formErrors)"
                        :start-time="[startTime, endTime]"
                    />
                </div>
            </div>

            <biz-form-textarea
                v-model="form.address"
                :label="i18n.address"
                :placeholder="i18n.address"
                rows="2"
                maxlength="500"
                :message="error('address', null, formErrors)"
            />

            <div class="box">
                <div class="columns">
                    <div class="column">
                        <biz-language-tab
                            class="is-right"
                            :locale-options="selectableLocales"
                            :selected-locale="selectedLocale"
                            @on-change-locale="onChangeLocale"
                        />
                    </div>
                </div>

                <template v-if="form.translations[selectedLocale]">
                    <biz-form-textarea
                        v-model="form.translations[ selectedLocale ].excerpt"
                        :label="i18n.excerpt"
                        :placeholder="i18n.excerpt"
                        rows="2"
                        maxlength="800"
                        :message="error('translations.'+selectedLocale+'.excerpt', null, formErrors)"
                    />

                    <biz-form-textarea
                        v-model="form.translations[ selectedLocale ].description"
                        :label="i18n.description"
                        :placeholder="i18n.description"
                        rows="5"
                        maxlength="65000"
                        :message="error('translations.'+selectedLocale+'.description', null, formErrors)"
                    />
                </template>
            </div>
        </form>

        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button @click="$emit('close')">
                            {{ i18n.cancel }}
                        </biz-button>

                        <biz-button
                            class="is-link ml-1"
                            type="button"
                            @click="submit"
                        >
                            {{ isNew ? i18n.create : i18n.update }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizFormDateTime from '@/Biz/Form/DateTime.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import BizLanguageTab from '@/Biz/LanguageTab.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import { cloneDeep, find, sortBy } from 'lodash';
    import { isBlank } from '@/Libs/utils';
    import { ref } from 'vue';
    import { confirmLeaveProgress, success as successAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/vue3';
    import { capitalCase } from 'change-case';

    export default {
        name: 'SpaceEventFormModal',

        components: {
            BizButton,
            BizFormDateTime,
            BizFormInput,
            BizFormTextarea,
            BizLanguageTab,
            BizModalCard,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            i18n: { default: () => ({
                title: 'Title',
                add_new_event: 'Add new event',
                edit_event: 'Edit event',
                started_and_ended_at: 'Started at and ended at',
                excerpt: 'Excerpt',
                description: 'Description',
                cancel: 'Cancel',
                create: 'Create',
                update: 'Update',
            }) },
        },

        props: {
            selectedEvent: { type: Object, default: () => {} },
            space: { type: Object, required: true },
        },

        emits: [
            'close',
            'after-submit',
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
                defaultLocale,
                localeOptions: localeOptions,
                selectedLocale: ref(selectedLocale),
                startTime: ref({ hours: 10, minutes: 0 }),
                endTime: ref({ hours: 17, minutes: 0 }),
            };
        },

        data() {
            return {
                eventRecord: {},
                formErrors: {},
                form: {},
            };
        },

        computed: {
            isNew() {
                return !this.eventRecord.id;
            },

            isFormReady() {
                return !isBlank(this.form);
            },

            selectableLocales() {
                if (!this.eventRecord.id) {
                    return [find(this.localeOptions, ['id', this.defaultLocale])];
                }
                return this.localeOptions;
            },

            startedEndedAt: {
                get() {
                    if (this.form.started_at) {
                        return [
                            this.form.started_at,
                            this.form.ended_at,
                        ];
                    }
                    return [];
                },
                set(newValue) {
                    if (newValue == null) {
                        this.form.started_at = this.form.ended_at = null;
                    } else {
                        this.form.started_at = newValue[0] ?? null;
                        this.form.ended_at = newValue[1] ?? null;
                    }
                }
            },
        },

        beforeMount: async function() {
            if (this.selectedEvent?.id) {
                this.eventRecord = await this.getRecord();

                this.setForm(this.selectedLocale);

            } else {

                this.form = useForm(this.newEvent());
            }

        },

        methods: {
            isBlank: isBlank,

            newEvent() {
                const event = {
                    title: null,
                    address: null,
                    started_at: null,
                    ended_at: null,
                    translations: {}
                };

                event.translations[this.selectedLocale] = this.newTranslation();

                return event;
            },

            newTranslation() {
                return {
                    excerpt: null,
                    description: null,
                };
            },

            getRecord() {
                const url = route(
                    'admin.spaces.events.show',
                    { space: this.space.id, event: this.selectedEvent.id }
                );

                return axios.get(url)
                    .then((response) => {
                        return response.data;
                    });
            },

            onChangeLocale(locale) {
                const self = this;

                if (locale == this.selectedLocale) {
                    return false;
                }

                if (this.form.isDirty) {
                    confirmLeaveProgress().then((result) => {
                        if (result.isDismissed) {
                            return false;
                        } else if (result.isConfirmed) {
                            self.setForm(locale);

                            self.selectedLocale = locale;
                        }
                    });
                } else {
                    self.setForm(locale);

                    self.selectedLocale = locale;
                }
            },

            setForm(locale) {
                const form = cloneDeep(this.eventRecord);

                form.translations = {};

                if (this.eventRecord.translations[locale]) {
                    form.translations[locale] = cloneDeep(this.eventRecord.translations[locale]);
                } else {
                    form.translations[locale] = this.newTranslation();
                }

                if (form.started_at) {
                    form.started_at = new Date(form.started_at);
                } else {
                    form.started_at = new Date();
                }

                if (form.ended_at) {
                    form.ended_at = new Date(form.ended_at);
                } else {
                    form.ended_at = new Date();
                }

                this.form = useForm(form);
            },

            submit() {
                const self = this;
                let method = null;
                let url = null;

                if (self.form.id) {
                    method = 'put';
                    url = route('admin.spaces.events.update', [self.space.id, self.form.id]);
                } else {
                    method = 'post';
                    url = route('admin.spaces.events.store', self.space.id);
                }

                axios[method](url, self.form.data())
                    .then((response) => {
                        self.eventRecord = response.data.event;

                        self.setForm(self.selectedLocale)

                        successAlert(response.data.message);

                        self.$emit('after-submit');

                        self.formErrors = {};
                    })
                    .catch((error) => {
                        self.formErrors = error.response.data.errors;
                    });
            },

            capitalCase,
        },
    };
</script>
