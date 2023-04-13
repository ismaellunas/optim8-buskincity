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
            @close="closeModal"
        >
            <template #actions="slotProps">
                <template v-if="computedMedia.id">
                    <biz-button
                        type="button"
                        :class="{'is-loading': isUploading, 'is-link': true}"
                        :disabled="isProcessing"
                        @click="updateImage"
                    >
                        {{ i18n.save }}
                    </biz-button>
                    <biz-button
                        type="button"
                        :class="{'is-loading': isUploading, 'is-primary': true}"
                        :disabled="isProcessing"
                        @click="saveAsImageConfirm"
                    >
                        {{ i18n.save_as_new }}
                    </biz-button>
                    <biz-button
                        type="button"
                        class="is-link is-light"
                        :disabled="isProcessing"
                        @click="closeModal"
                    >
                        {{ i18n.cancel }}
                    </biz-button>
                </template>

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
    import { confirm as confirmAlert, success as successAlert } from '@/Libs/alert';
    import { getCanvasBlob, useModelWrapper } from '@/Libs/utils';
    import { useForm } from '@inertiajs/vue3';

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
                croppedImageType: "image/png",
                isUploading: false,
            };
        },

        computed: {
            previewFileSrc() {
                return this.computedMedia?.file_url
                    ?? this.computedMedia?.file
                    ?? '';
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

                if (media.file_type === "video") {
                    return "far fa-file-video";
                } else if (media.extension) {
                    if (media.extension === "pdf") {
                        return "far fa-file-pdf";
                    } else if (media.extension.startsWith('doc')) {
                        return "far fa-file-word";
                    } else if (media.extension.startsWith('ppt')) {
                        return "far fa-file-powerpoint";
                    } else if (media.extension.startsWith('xls')) {
                        return "far fa-file-excel";
                    }
                }
                return "far fa-file-alt";
            },

            updateImage() {
                const self = this;
                const media = this.computedMedia;
                const url = route(this.baseRouteName+'.update-image', media.id);
                const cropper = this.cropper;

                self.isUploading = true;

                self.getCropperBlob().then((blob) => {
                    cropper.disable();

                    const form = useForm({
                        image: blob,
                        file_name: media.file_name,
                    });

                    form.post(url, {
                        preserveState: true,
                        preserveScroll: true,
                        onStart: () => self.onStartLoadingOverlay(),
                        onSuccess: (page) => {
                            const updatedMedia = page.props.records.data.find((record) => record.id === media.id);

                            self.computedMedia.file_url = updatedMedia.file_url;
                            self.closeModal();
                        },
                        onError: (errors) => {
                            if (self.$page.props.debug) {
                                console.log(error);
                            }
                        },
                        onFinish: () => {
                            if (cropper) {
                                cropper.enable();
                            }
                            self.isUploading = false;
                            self.onEndLoadingOverlay();
                        },
                    });
                });
            },

            saveAsImageConfirm() {
                confirmAlert("Are you sure?", "You will create a new image")
                    .then((result) => result.isConfirmed ? this.saveAsImage() : false);
            },

            saveAsImage() {
                const self = this;
                const media = this.computedMedia;
                const url = route(this.baseRouteName+'.save-as-image', media.id);

                self.isUploading = true;

                self.getCropperBlob().then((blob) => {

                    self.cropper.disable();

                    const form = useForm({
                        image: blob,
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
                            if (self.$page.props.debug) {
                                console.log(error);
                            }
                        },
                        onFinish: () => {
                            if (self.cropper) {
                                self.cropper.enable();
                            }
                            self.isUploading = false;
                            self.onEndLoadingOverlay();
                        },
                    });
                });
            },

            updateFile() {
                const self = this;

                self.getCropperBlob()
                    .then((blob) => {
                        self.computedMedia.file_url = URL.createObjectURL(blob);
                        self.computedMedia.file = blob;
                        self.computedMedia.is_image = true;
                        self.closeModal();
                    });
            },

            /* @return Promise */
            getCropperBlob() {
                return getCanvasBlob(
                    this.cropper.getCroppedCanvas(),
                    this.croppedImageType
                );
            },
        },
    }
</script>