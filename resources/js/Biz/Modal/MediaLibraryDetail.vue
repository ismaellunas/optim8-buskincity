<template>
    <biz-modal-card
        content-class="is-huge"
        :is-close-hidden="true"
        @close="closeModal()"
    >
        <template #header>
            <p class="modal-card-title">
                {{ i18n.edit_resource }}
            </p>

            <biz-button
                aria-label="close"
                class="delete is-primary"
                type="button"
                @click="closeModal()"
            />
        </template>

        <biz-error-notifications
            :errors="errorMessage"
        />

        <biz-media-library-detail
            :media="form"
            :allow-multiple="false"
            :is-ajax="false"
            :is-save-as-new-enabled="false"
            @on-close-edit-modal="closeModal()"
            @on-update-media="onUpdateMedia"
        />

        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div class="column">
                    <div class="buttons is-right">
                        <biz-button
                            type="button"
                            class="is-primary"
                            @click="onSave()"
                        >
                            {{ i18n.save }}
                        </biz-button>

                        <biz-button
                            type="button"
                            @click="$emit('close')"
                        >
                            {{ i18n.cancel }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizMediaLibraryDetail from '@/Biz/MediaLibraryDetail.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import { buildFormData } from '@/Libs/utils';
    import { cloneDeep, isEmpty } from 'lodash';
    import { generateNewTranslation } from '@/Libs/media-translation';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { reactive } from 'vue';
    import { usePage } from '@inertiajs/vue3';

    export default {
        name: "BizModalMediaLibraryDetail",

        components: {
            BizButton,
            BizErrorNotifications,
            BizMediaLibraryDetail,
            BizModalCard,
        },

        mixins: [
            MixinHasLoader,
        ],

        inject: ['i18n'],

        props: {
            media: { type: Object, required: true, },
        },

        emits: [
            'close',
            'update-media',
            'select-media',
        ],

        setup(props) {
            let defaultLocale = usePage().props.defaultLanguage;
            let media = cloneDeep(props.media);
            let form = reactive(media);
            let translations = {};

            if (isEmpty(media.translations)) {
                translations[defaultLocale] = generateNewTranslation();
            } else {
                media.translations.forEach(translation => {
                    translations[translation.locale] = {
                        alt: translation.alt ?? null,
                        description: translation.description ?? null,
                    };
                });

                if (!translations[defaultLocale]) {
                    translations[defaultLocale] = generateNewTranslation();
                }
            }

            form.file_name = media.file_name_without_extension;
            form.translations = translations;

            return {
                form,
            };
        },

        data() {
            return {
                errorMessage: {},
            };
        },

        methods: {
            onSave() {
                const self = this;
                const currentForm = [
                    self.form
                ];

                const formData = new FormData();
                buildFormData(formData, currentForm);

                self.onStartLoadingOverlay();

                axios.post(
                    route('admin.api.media.store'),
                    formData,
                    {
                        headers: {'Content-Type': 'multipart/form-data'}
                    }
                )
                    .then((response) => {
                        self.onSuccessSubmit(response, self.i18n.update_media_success);

                        self.errorMessage = null;
                    })
                    .catch((error) => {
                        oopsAlert();

                        self.errorMessage = {
                            default: error.response.data.errors,
                        };
                    }).then(() => {
                        self.onEndLoadingOverlay();

                        self.isInputDisabled = false;
                    });
            },

            onSuccessSubmit(response, message = null) {
                if (message) {
                    successAlert(message);
                }

                this.closeModal();

                this.$emit('update-media', response.data[0]);
            },

            closeModal() {
                this.$emit('close');
            },

            onUpdateMedia(event) {
                this.$emit('update-media', event)
            }
        },
    }
</script>