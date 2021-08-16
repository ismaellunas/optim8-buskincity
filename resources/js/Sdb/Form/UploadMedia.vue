<template>
    <div>
        <form @submit.prevent="submitFile">
            <div class="field">
                <div class="control">
                    <sdb-input-file
                        v-model="file"
                        class="is-fullwidth"
                        :accept="acceptedTypes"
                        :is-name-displayed="false"
                        @on-file-picked="onFilePicked"
                    />
                </div>
            </div>
        </form>

        <sdb-modal-card
            v-if="isModalOpen"
            :content-class="['is-huge']"
            :is-close-hidden="true"
        >
            <template v-slot:header>
                <p class="modal-card-title mb-1">Image Editor : {{ file?.name ?? ''}}</p>
                <sdb-button
                    aria-label="close"
                    class="delete is-primary"
                    type="button"
                    :disabled="isUploading"
                    @click="closeModal()"
                />
            </template>

            <template v-slot:footer>
                <template v-if="isCropMode">
                    <div class="column">
                        <div class="columns">
                            <div class="column">
                                <div class="buttons has-addons">
                                    <!-- ratio -->
                                </div>
                            </div>
                            <div class="column">
                                <sdb-button @click="reset">Reset</sdb-button>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="is-pulled-right">
                            <sdb-button @click="disableCropMode">Cancel</sdb-button>
                            <sdb-button class="is-primary" @click="cropAndReplace">Done</sdb-button>
                        </div>
                        <div class="is-clearfix"></div>
                    </div>
                </template>
                <template v-else>
                    <div class="column is-8">
                        <div class="columns">
                            <div class="column">
                                <div class="buttons has-addons">
                                    <sdb-button
                                        :class="{'is-primary': (aspectRatio == null)}"
                                        :disabled="isUploading"
                                        @click="setAspectRatio(null)"
                                    >
                                        Free
                                    </sdb-button>
                                    <sdb-button
                                        :class="{'is-primary': (aspectRatio == 16/9)}"
                                        :disabled="isUploading"
                                        @click="setAspectRatio(16/9)"
                                    >
                                        16:9
                                    </sdb-button>
                                    <sdb-button
                                        :class="{'is-primary': (aspectRatio == 4/3)}"
                                        :disabled="isUploading"
                                        @click="setAspectRatio(4/3)"
                                    >
                                        4:3
                                    </sdb-button>
                                    <sdb-button
                                        :class="{'is-primary': (aspectRatio == 1)}"
                                        :disabled="isUploading"
                                        @click="setAspectRatio(1)"
                                    >
                                        1:1
                                    </sdb-button>
                                </div>
                            </div>
                            <div class="column">
                                <sdb-button
                                    :disabled="isUploading"
                                    @click="reset"
                                >
                                    Reset
                                </sdb-button>
                                <sdb-button
                                    :disabled="isUploading"
                                    @click="rotateLeft"
                                >
                                    <span class="icon is-small">
                                        <i class="fas fa-undo-alt"></i>
                                    </span>
                                </sdb-button>
                                <sdb-button
                                    :disabled="isUploading"
                                    @click="rotateRight"
                                >
                                    <span class="icon is-small">
                                        <i class="fas fa-redo-alt"></i>
                                    </span>
                                </sdb-button>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="is-pulled-right">
                            <sdb-button
                                class="button"
                                :disabled="isUploading"
                                @click="closeModal"
                            >
                                Cancel</sdb-button>
                            <sdb-button
                                @click="submitFile"
                                :class="{'is-loading': isUploading}"
                            >
                                Upload
                            </sdb-button>
                        </div>
                        <div class="is-clearfix"></div>
                    </div>
                </template>
            </template>

            <vue-cropper
                alt="Source Image"
                ref="cropper"
                :auto-crop-area="1"
                :src="previewFileSrc"
                @crop="updateImageData"
            />
        </sdb-modal-card>
    </div>
</template>

<script>
    import NProgress from 'nprogress';
    import HasModalMixin from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import SdbInputFile from '@/Sdb/InputFile';
    import SdbModalCard from '@/Sdb/ModalCard';
    import Compressor from 'compressorjs';
    import { useModelWrapper, isBlank } from '@/Libs/utils';

    import VueCropper from 'vue-cropperjs';
    import 'cropperjs/dist/cropper.css';

    export default {
        mixins: [
            HasModalMixin,
        ],
        components: {
            SdbButton,
            SdbInputFile,
            SdbModalCard,
            VueCropper,
        },
        props: {
            entityId: {},
            modelValue: {},
            uploadRoute: {},
            //isUploading: {type: Boolean, default: false},
        },
        setup(props, { emit }) {
            return {
                imageSrc: useModelWrapper(props, emit),
                //isUploading: useModelWrapper(props, emit, 'isUploading')
            };
        },
        data() {
            return {
                isUploading: false,
                acceptedTypes: ['image/png', 'image/jpeg'],
                file: null,
                previewFile: null,
                previewFileSrc: null,
                mode: null,
                modeOptions: {
                    crop: "crop",
                },
                aspectRatio: null,
                imageData: null,
            }
        },
        methods: {
            submitFile() {
                const self = this;
                const canvas = this.getCropper().getCroppedCanvas();
                canvas.toBlob((blob) => {
                    new Compressor(blob, {
                        quality: 0.8,
                        success(result) {
                            self.uploadImage(result, self.file.name)
                        },
                        error(err) {
                            console.log(err.message);
                        },
                    });
                });
            },
            uploadImage(file, fileName) {
                const self = this;
                let formData = new FormData();

                formData.append('image', file, fileName);

                if (!isBlank(this.entityId)) {
                    formData.append('id', this.entityId);
                }

                self.isUploading = true;
                self.getCropper().disable();

                axios.post(this.uploadRoute, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: event => {
                        NProgress.set(Math.round( (event.loaded * 100) / event.total ));
                    }
                }).then(function(response) {
                    self.imageSrc = response.data.imagePath;
                    self.resetData();
                    self.closeModal();
                    self.$emit('on-media-upload-success', response);
                })
                .catch(function(error) {
                    console.log(error);
                }).then(() => {
                    self.isUploading = false;
                    self.getCropper().enable();
                });
            },
            resetData() {
                this.file = null;
                this.previewFile = null;
                this.previewFileSrc = null;
            },
            onFilePicked(event) {
                this.previewFileSrc = event.target.result;
                this.openModal();
            },
            cropAndReplace() {
                const cropper = this.getCropper();
                const canvas = cropper.getCroppedCanvas();
                this.previewFileSrc = canvas.toDataURL();
                cropper
                    .setDragMode("move")
                    .replace(this.previewFileSrc, false);

                this.disableCropMode();
            },
            getCropper() {
                return this.$refs.cropper;
            },
            rotateRight() {
                this.$refs.cropper.rotate(90);
            },
            rotateLeft() {
                this.$refs.cropper.rotate(-90);
            },
            enableCropMode() {
                this.mode = this.modeOptions.crop;
                const cropper = this.getCropper();
                cropper.initCrop();
                cropper.setDragMode("crop");
            },
            disableCropMode() {
                this.getCropper()
                    .reset()
                    .clear()
                    .setDragMode('move');
                this.mode = null;
            },
            reset() {
                this.getCropper()
                    .reset()
                    .setAspectRatio(null);
            },
            setAspectRatio(ratio) {
                this.getCropper().setAspectRatio(ratio);
                this.aspectRatio = ratio;
            },
            updateImageData() {
                this.imageData = this.$refs.cropper.getData(true)
            },
        },
        computed: {
            canUpload() {
                return !isBlank(this.file);
            },
            isCropMode() {
                return this.mode === this.modeOptions.crop;
            }
        }
    }
</script>
