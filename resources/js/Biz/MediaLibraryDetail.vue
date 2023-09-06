<template>
    <div>
        <div class="columns">
            <div class="column">
                <div
                    v-if="isImage(computedMedia)"
                    class="card"
                >
                    <div class="card-image">
                        <biz-image
                            :src="previewFileSrc"
                            :img-style="{ maxHeight: 500+'px' }"
                        />
                    </div>
                    <footer class="card-footer">
                        <biz-button
                            class="card-footer-item is-borderless is-shadowless"
                            type="button"
                            @click="openModal"
                        >
                            {{ i18n.edit_image }}
                        </biz-button>
                    </footer>
                </div>
                <div
                    v-else
                    class="card"
                    style="height: 90%"
                >
                    <div
                        class="card-image"
                        style="height: inherit"
                    >
                        <span
                            class="icon is-large"
                            style="width: 100%"
                        >
                            <span class="fa-stack fa-lg">
                                <i :class="[mediaIconThumbnail(computedMedia), 'fa-6x']" />
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="column">
                <media-form
                    :media="computedMedia"
                    :is-ajax="isAjax"
                />

                <div
                    v-if="allowMultiple"
                    class="has-text-right mt-4"
                >
                    <biz-button
                        type="button"
                        class="is-danger"
                        @click="$emit('on-delete-edit')"
                    >
                        {{ i18n.delete }}
                    </biz-button>
                </div>
            </div>
        </div>

        <biz-modal-image-editor
            v-if="isModalOpen"
            v-model="computedMedia.file_url"
            v-model:cropper="cropper"
            :cropped-image-type="croppedImageType"
            :file-name="computedMedia.file_name"
            :is-processing="isProcessing"
            :dimension="injectMediaDimension"
            @close="closeModal"
        >
            <template #leftActions>
                <div class="buttons">
                    <biz-button
                        type="button"
                        :disabled="isProcessing"
                        @click="closeModal"
                    >
                        {{ i18n.cancel }}
                    </biz-button>
                </div>
            </template>

            <template #actions="slotProps">
                <div
                    v-if="computedMedia.id"
                    class="buttons is-right"
                >
                    <biz-button
                        type="button"
                        :class="{'is-loading': isUploading, 'is-primary': true}"
                        :disabled="isProcessing"
                        @click="updateImage()"
                    >
                        {{ i18n.save }}
                    </biz-button>
                    <biz-button
                        type="button"
                        :class="{'is-loading': isUploading, 'is-link': true}"
                        :disabled="isProcessing"
                        @click="saveAsImageConfirm()"
                    >
                        {{ i18n.save_as_new }}
                    </biz-button>
                </div>

                <template v-else>
                    <biz-button
                        type="button"
                        :class="{ 'is-loading': isUploading, 'is-link': true }"
                        :disabled="isProcessing"
                        @click="updateFile"
                    >
                        {{ i18n.done }}
                    </biz-button>
                </template>
            </template>
        </biz-modal-image-editor>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButton from '@/Biz/Button.vue';
    import BizImage from '@/Biz/Image.vue';
    import BizModalImageEditor from '@/Biz/Modal/ImageEditor.vue';
    import MediaForm from '@/Pages/Media/Form.vue';
    import icon from '@/Libs/icon-class';
    import { confirm as confirmAlert, success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { getBlob, getCanvas } from '@/Libs/crop-helper';
    import { startsWith } from 'lodash';
    import { useForm } from '@inertiajs/vue3';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizMediaLibraryDetail',

        components: {
            BizButton,
            BizImage,
            MediaForm,
            BizModalImageEditor,
        },

        mixins: [
            MixinHasModal,
            MixinHasLoader,
        ],

        inject: {
            i18n: {
                default: () => ({
                    edit_image : 'Edit image',
                    delete : 'Delete',
                    save : 'Save',
                    save_as_new : 'Save as new',
                    cancel : 'Cancel',
                    done : 'Done',
                })
            },
            injectMediaDimension: {
                default: () => {},
            },
        },

        props: {
            allowMultiple: { type: Boolean, default: false, },
            baseRouteName: {type: String, default: 'admin.media'},
            isAjax: {type: Boolean, default: false},
            isProcessing: {type: Boolean, default: false},
            media: { type: Object, required: true },
        },

        emits: [
            'on-close-edit-modal',
            'on-delete-edit',
        ],

        setup(props, {emit}) {
            return {
                computedMedia: useModelWrapper(props, emit, 'media'),
            };
        },

        data() {
            return {
                cropper: null,
                isUploading: false,
                icon,
            };
        },

        computed: {
            previewFileSrc() {
                return this.computedMedia?.file_url
                    ?? this.computedMedia?.file
                    ?? '';
            },
            croppedImageType() {
                let imageType = null;

                if (this.media?.file?.type) {
                    imageType = this.media.file.type;
                } else if (this.media?.extension) {
                    imageType = 'image/' + this.media.extension;
                }

                return imageType == 'image/png' ? imageType : 'image/jpeg';
            },
        },

        methods: {
            isImage(media) {
                return (
                    (media?.is_image)
                    || (media?.file && media.file.type.startsWith("image"))
                );
            },

            mediaIconThumbnail(media) {
                if (!media) return;

                const type = media.file?.type ?? media.file_type;
                const extension = media.file?.name.split('.').pop()
                    ?? media.extension;

                if (startsWith(type, 'video')) {
                    return this.icon.fileVideo;
                } else if (startsWith(extension, 'pdf')) {
                    return this.icon.filePdf;
                } else if (startsWith(extension, 'doc')) {
                    return this.icon.fileWord;
                } else if (startsWith(extension, 'ppt')) {
                    return this.icon.filePowerpoint;
                } else if (startsWith(extension, 'xls')) {
                    return this.icon.fileExcel;
                }

                return this.icon.file;
            },

            async updateImage() {
                const self = this;
                const media = this.computedMedia;
                const url = route(this.baseRouteName+'.update-image', media.id);
                const cropper = this.cropper;

                self.isUploading = true;

                cropper.disable();

                const form = useForm({
                    image: await getBlob(cropper, this.croppedImageType),
                    file_name: media.file_name,
                });

                form.post(url, {
                    preserveState: true,
                    preserveScroll: true,
                    onStart: () => self.onStartLoadingOverlay(),
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);

                        const updatedMedia = page.props.records.data.find((record) => record.id === media.id);

                        self.computedMedia.file_url = updatedMedia.file_url;

                        self.closeModal();
                    },
                    onError: (errors) => {
                        oopsAlert();

                        if (self.$page.props.debug) {
                            console.log(errors);
                        }
                    },
                    onFinish: () => {
                        cropper.enable();

                        self.isUploading = false;
                        self.onEndLoadingOverlay();
                    },
                });
            },

            saveAsImageConfirm(cropper) {
                confirmAlert("Are you sure?", "You will create a new image")
                    .then((result) => result.isConfirmed ? this.saveAsImage(cropper) : false);
            },

            async saveAsImage() {
                const self = this;
                const media = this.computedMedia;
                const url = route(this.baseRouteName+'.save-as-image', media.id);
                const cropper = this.cropper;

                self.isUploading = true;

                cropper.disable();

                const form = useForm({
                    image: await getBlob(cropper, this.croppedImageType),
                    filename: media.file_name,
                });

                form.post(url, {
                    preserveState: true,
                    preserveScroll: true,
                    onStart: () => self.onStartLoadingOverlay(),
                    onSuccess: (page) => {
                        self.closeModal();

                        successAlert(page.props.flash.message);

                        self.$emit('on-close-edit-modal');
                    },
                    onError: (errors) => {
                        oopsAlert();

                        if (self.$page.props.debug) {
                            console.error(error);
                        }
                    },
                    onFinish: () => {
                        cropper.enable();

                        self.isUploading = false;
                        self.onEndLoadingOverlay();
                    },
                });
            },

            async updateFile(setFile) {
                this.computedMedia.file = await getBlob(
                    this.cropper,
                    this.croppedImageType
                );

                this.computedMedia.file_url = getCanvas(this.cropper, 600)
                    .toDataURL('image/jpeg', 0.8);

                this.computedMedia.is_image = true;

                this.closeModal();
            },
        },
    };
</script>
