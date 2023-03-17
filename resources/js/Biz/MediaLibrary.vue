<template>
    <div>
        <div
            v-if="isUploadEnabled"
            class="columns"
        >
            <div class="column is-full">
                <div class="content box">
                    <div class="field">
                        <div class="control">
                            <biz-file-upload
                                :key="fileUploadKey"
                                :accepted-types="acceptedMimesTypes"
                                :allow-multiple="allowMultiple"
                                :disabled="isProcessing"
                                :max-files="maxFiles"
                                :max-file-size="maxFileSize"
                                required
                                @on-update-files="onUpdateFiles"
                            />
                        </div>

                        <p
                            v-if="hasInstructions"
                            class="help is-info"
                        >
                            <ul :style="instructionStyle">
                                <li
                                    v-for="note, index in instructions"
                                    :key="index"
                                >
                                    {{ note }}
                                </li>
                            </ul>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="columns is-multiline">
            <div class="column is-full">
                <div class="columns">
                    <div class="column">
                        <biz-filter-search
                            v-model="term"
                            @search="search"
                        />
                    </div>

                    <div
                        v-if="isFilterEnabled"
                        class="column"
                    >
                        <biz-dropdown
                            :close-on-click="false"
                        >
                            <template #trigger>
                                <span>Filter</span>
                                <span
                                    v-if="types.length > 0"
                                    class="ml-1"
                                >
                                    ({{ types.length }})
                                </span>
                                <span class="icon is-small">
                                    <i
                                        :class="icon.angleDown"
                                        aria-hidden="true"
                                    />
                                </span>
                            </template>

                            <biz-dropdown-item
                                v-for="(type, typeIndex) in typeOptions"
                                :key="typeIndex"
                            >
                                <biz-checkbox
                                    v-model:checked="types"
                                    :value="typeIndex"
                                    @change="$emit('on-type-changed', types)"
                                >
                                    &nbsp;{{ type }}
                                </biz-checkbox>
                            </biz-dropdown-item>
                        </biz-dropdown>
                    </div>

                    <div class="column is-one-fifth">
                        <biz-buttons-display-view
                            v-model="view"
                            class="is-right"
                            @on-view-changed="$emit('on-view-changed', $event)"
                        />
                    </div>
                </div>
            </div>

            <div class="column is-full">
                <biz-table-info :records="records" />
            </div>

            <div class="column is-full">
                <component
                    :is="isGalleryView ? 'BizMediaGallery' : 'BizMediaList'"
                    :media="records.data"
                    :is-delete-enabled="isDeleteEnabled"
                    :is-download-enabled="isDownloadEnabled"
                    :is-edit-enabled="isEditEnabled"
                    @on-delete-clicked="onDeleteRecord"
                    @on-edit-clicked="openEditModal"
                >
                    <template
                        #itemActions="{ mediumItem }"
                    >
                        <slot
                            name="itemActions"
                            :medium-item="mediumItem"
                        />
                    </template>
                </component>
            </div>

            <div
                v-if="isPaginationDisplayed"
                class="column is-full"
            >
                <biz-pagination
                    :is-ajax="isAjax"
                    :links="records?.links ?? []"
                    :query-params="queryParams"
                />
            </div>
        </div>

        <biz-modal-card
            v-if="isEditing"
            content-class="is-huge"
            :is-close-hidden="true"
            @close="closeEditModal"
        >
            <template #header>
                <p class="modal-card-title">
                    Media Detail
                </p>
                <biz-button
                    aria-label="close"
                    class="delete is-primary"
                    type="button"
                    @click="closeEditModal"
                />
            </template>

            <biz-error-notifications
                :errors="$page.props.errors"
            />

            <template
                v-for="(media, index) in formMedia"
                :key="index"
            >
                <biz-media-library-detail
                    :media="formMedia[index]"
                    :allow-multiple="allowMultiple"
                    :is-ajax="isAjax"
                    :is-processing="isProcessing"
                    @on-close-edit-modal="closeEditModal()"
                    @on-delete-edit="onDeleteEdit(index)"
                />

                <hr v-if="formMedia.length != (index + 1)">
            </template>

            <template #footer>
                <div
                    class="columns"
                    style="width: 100%"
                >
                    <div class="column">
                        <div class="buttons is-pulled-right">
                            <biz-button
                                type="button"
                                class="is-link"
                                @click="onSubmit()"
                            >
                                Submit
                            </biz-button>

                            <biz-button
                                class="is-link is-light ml-2"
                                type="button"
                                @click="closeEditModal"
                            >
                                Cancel
                            </biz-button>
                        </div>
                    </div>
                </div>
            </template>
        </biz-modal-card>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonsDisplayView from '@/Biz/ButtonsDisplayView.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizDropdown from '@/Biz/Dropdown.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFileUpload from '@/Biz/FileUpload.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizImage from '@/Biz/Image.vue';
    import BizInputFile from '@/Biz/InputFile.vue';
    import BizMediaGallery from '@/Biz/Media/Gallery.vue';
    import BizMediaLibraryDetail from '@/Biz/MediaLibraryDetail.vue';
    import BizMediaList from '@/Biz/Media/List.vue';
    import BizModal from '@/Biz/Modal.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizModalImageEditor from '@/Biz/Modal/ImageEditor.vue';
    import BizPagination from '@/Biz/Pagination.vue';
    import BizTableInfo from '@/Biz/TableInfo.vue';
    import icon from '@/Libs/icon-class';
    import MediaForm from '@/Pages/Media/Form.vue';
    import { acceptedFileTypes, acceptedImageTypes } from '@/Libs/defaults';
    import { confirm as confirmAlert, confirmDelete, success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { extensionToMimes, buildFormData } from '@/Libs/utils';
    import { includes, isEmpty, cloneDeep } from 'lodash';
    import { ref } from "vue";
    import { useForm, usePage } from '@inertiajs/vue3';

    function generateNewTranslation() {
        return {
            alt: '',
            description: '',
        };
    };

    export default {
        name: 'MediaLibrary',

        components: {
            BizButton,
            BizButtonsDisplayView,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizErrorNotifications,
            BizFileUpload,
            BizFilterSearch,
            BizFormField,
            BizImage,
            BizInputFile,
            BizMediaGallery,
            BizMediaLibraryDetail,
            BizMediaList,
            BizModal,
            BizModalCard,
            BizModalImageEditor,
            BizPagination,
            BizTableInfo,
            MediaForm,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
            MixinHasPageErrors,
        ],

        props: {
            acceptedTypes: {type: Array, default: acceptedFileTypes},
            allowMultiple: { type: Boolean, default: false, },
            baseRouteName: {type: String, default: 'admin.media'},
            instructions: {type: Array, default: () => []},
            isAjax: {type: Boolean, default: false},
            isDeleteEnabled: {type: Boolean, default: true},
            isDownloadEnabled: {type: Boolean, default: true},
            isEditEnabled: {type: Boolean, default: true},
            isFilterEnabled: {type: Boolean, default: false},
            isPaginationDisplayed: {type: Boolean, default: true},
            isSelectEnabled: {type: Boolean, default: false},
            isUploadEnabled: {type: Boolean, default: true},
            maxFiles: { type: Number, default: 1, },
            maxFileSize: { type: [String, Number], default: null, },
            queryParams: {type: Object, default: () => {}},
            records: {type: Object, required: true},
            search: {type: Function, required: true},
            typeOptions: {type: Object, default() {
                return {
                    'image': "Image",
                    'video': "Video",
                    'document': "Document",
                    'spreadsheet': "Spreadsheet",
                    'presentation': "Presentation",
                };
            }},
        },

        emits: [
            'on-media-submitted',
            'on-type-changed',
            'on-view-changed',
        ],

        setup(props) {
            return {
                defaultLocale: usePage().props.defaultLanguage,
                term: ref(props.queryParams.term),
                types: ref(props.queryParams?.types ?? []),
                view: ref(props.queryParams.view ?? 'gallery'),
            };
        },

        data() {
            return {
                formMedia: [],
                fileUploadKey: 0,
                isEditing: false,
                isDeleting: false,
                messageText: {
                    successSaveAsMedia: "A new media has been created",
                },
                icon,
            };
        },

        computed: {
            isProcessing() {
                return this.isDeleting;
            },

            isGalleryView() {
                return this.view === 'gallery';
            },

            hasInstructions() {
                return !isEmpty(this.instructions);
            },

            instructionStyle() {
                return {
                    'list-style-type': 'none',
                    'padding': 0,
                    'margin': 0
                };
            },

            acceptedMimesTypes() {
                return extensionToMimes(this.acceptedTypes);
            },

            isFormMediaEmpty() {
                return this.formMedia.length == 0;
            },
        },

        methods: {
            openEditModal(media) {
                this.isEditing = true;

                if (media) {
                    let form = cloneDeep(media);
                    let translations = {};

                    if (isEmpty(media.translations)) {
                        translations[this.defaultLocale] = generateNewTranslation();
                    } else {
                        media.translations.forEach(translation => {
                            translations[translation.locale] = {
                                alt: translation.alt ?? null,
                                description: translation.description ?? null,
                            };
                        });

                        if (!translations[this.defaultLocale]) {
                            translations[this.defaultLocale] = generateNewTranslation();
                        }
                    }

                    form.file_name = media.file_name_without_extension;
                    form.translations = translations;

                    this.formMedia.push(form);
                }
            },

            closeEditModal() {
                this.isEditing = false;
                this.formMedia = [];
                this.fileUploadKey += 1;
            },

            onDeleteRecord(record) {
                const self = this;

                if (!record.canDeleted) {
                    confirmAlert(
                        'Delete Media?',
                        'This media is still being used in another place. If you delete this media, it may have an effect on that other place.',
                        'Yes',
                        {
                            icon: 'warning'
                        }
                    ).then((result) => {
                        if (result.isConfirmed) {
                            self.deleteRecord(record);
                        }
                    });
                } else {
                    self.deleteRecord(record);
                }
            },

            deleteRecord(record) {
                const self = this;

                confirmDelete('Are you sure?').then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', {id: record.id}),
                            {
                                onStart: visit => {
                                    self.onStartLoadingOverlay();

                                    self.isDeleting = true;
                                },
                                onSuccess: page => {
                                    successAlert(page.props.flash.message);
                                },
                                onError: errors => {
                                    oopsAlert();
                                },
                                onFinish: visit => {
                                    self.onEndLoadingOverlay();

                                    self.isDeleting = false;
                                },
                            }
                        );
                    }
                });
            },

            onUpdateFiles(files) {
                const self = this;

                self.formMedia = [];

                files.forEach(function (file) {
                    let translations = {};

                    translations[self.defaultLocale] = generateNewTranslation();

                    self.formMedia.push({
                        file: file,
                        file_url: URL.createObjectURL(file),
                        file_name: self.fileNameFormatter(file),
                        is_image: includes(
                            acceptedImageTypes,
                            "." + file.type.split('/')[1]
                        ),
                        translations: translations
                    })
                });

                if (files.length > 0) {
                    this.openEditModal();
                }
            },

            fileNameFormatter(file) {
                return file.name
                    .split('.').slice(0, -1).join('.')
                    .toLowerCase()
                    .replace(/[^a-z0-9]/gi, '-')
                    .replace(/-+/g, "-");
            },

            onSubmit() {
                const self = this;
                const currentForm = self.formMedia;

                let url = null;

                if (self.isAjax) {
                    url = route('admin.api.media.store');
                } else {
                    url = route('admin.media.store');
                }

                self.onStartLoadingOverlay();
                self.isInputDisabled = true;

                if (self.isAjax) {
                    const formData = new FormData();
                    buildFormData(formData, currentForm);

                    axios.post(
                        url,
                        formData,
                        {headers: {'Content-Type': 'multipart/form-data'}}
                    )
                        .then((response) => {
                            self.onSuccessSubmit(response);
                        })
                        .catch(() => {
                            oopsAlert();
                        }).then(() => {
                            self.onEndLoadingOverlay();

                            self.isInputDisabled = false;
                        });
                } else {
                    const form = useForm(currentForm);
                    form.post(url, {
                        onSuccess: (page) => {
                            self.onSuccessSubmit(
                                page,
                                page.props.flash.message
                            );
                        },
                        onError: () => {
                            oopsAlert();
                        },
                        onFinish: () => {
                            self.onEndLoadingOverlay();

                            self.isInputDisabled = false;
                        },
                    });
                }
            },

            onSuccessSubmit(response, message = null) {
                if (message) {
                    successAlert(message);
                }

                this.closeEditModal();

                this.formMedia = [];
                this.fileUploadKey += 1;

                this.$emit('on-media-submitted', response);
            },

            onDeleteEdit(index) {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.formMedia.splice(index, 1);

                        if (self.isFormMediaEmpty) {
                            self.closeEditModal();

                            self.fileUploadKey += 1;
                        }
                    }
                });
            },
        },
    }
</script>
