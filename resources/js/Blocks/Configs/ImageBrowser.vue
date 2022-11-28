<template>
    <div>
        <biz-form-field
            label-class="is-size-7"
        >
            <template #label>
                {{ label }}
            </template>

            <biz-button
                v-if="!hasImage"
                type="button"
                class="is-small"
                :disabled="!can.media.browse"
                @click="openModal()"
            >
                <span>Open Media</span>
                <span class="icon is-small">
                    <i :class="icon.image" />
                </span>
            </biz-button>

            <biz-button
                v-else
                type="button"
                class="is-danger is-small component-configurable"
                :disabled="!can.media.browse"
                @click="deleteImage()"
            >
                <span>Remove Image</span>
                <span class="icon is-small">
                    <i :class="icon.remove" />
                </span>
            </biz-button>

            <slot name="note" />
        </biz-form-field>

        <biz-modal-media-browser
            v-if="isModalOpen"
            class="component-configurable"
            :data="modalImages"
            :is-download-enabled="can.media.read"
            :is-upload-enabled="can.media.add"
            :query-params="imageListQueryParams"
            :search="search"
            @close="closeModal()"
            @on-clicked-pagination="getImagesList"
            @on-media-selected="selectImage"
            @on-media-submitted="updateImage"
            @on-view-changed="setView"
        />
    </div>
</template>

<script>
    import MixinContentHasMediaLibrary from '@/Mixins/ContentHasMediaLibrary';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser';
    import BizFormField from '@/Biz/Form/Field';
    import BizButton from '@/Biz/Button';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { forEach } from 'lodash';
    import { inject } from "vue";
    import icon from '@/Libs/icon-class';

    export default {
        name: 'ImageBrowser',

        components: {
            BizFormField,
            BizButton,
            BizModalMediaBrowser,
        },

        mixins: [
            MixinContentHasMediaLibrary,
            MixinHasModal,
        ],

        inject: ['can'],

        inheritAttrs: false,

        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: {
                type: String,
                default: ""
            },
            modelValue: {
                type: [Object, Array, String, Number, Boolean, null],
                required: true
            },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
                dataImages: inject('dataImages'),
                pageMedia: inject('dataMedia'),
                selectedLocale: inject('selectedLocale'),
            };
        },

        data() {
            return {
                icon,
                images: this.dataImages,
                modalImages: [],
            };
        },

        computed: {
            hasImage() {
                return (!isBlank(this.computedValue));
            },
        },

        methods: {
            selectImage(image) { /* @override MixinContentHasMediaLibrary */
                let hasImage = false;
                const locale = this.selectedLocale;
                if (!isBlank(this.computedValue)) {
                    this.detachImageFromMedia(this.computedValue, this.pageMedia);
                }

                if (!this.images[ locale ]) {
                    this.images[ locale ] = [];
                }

                forEach(this.images[ locale ], function(value) {
                    if (value.id === image.id) {
                        value = image;
                        hasImage = true;
                    }
                });

                if (!hasImage) {
                    this.images[locale].push(image);
                }

                this.computedValue = image.id;

                this.attachImageToMedia(image.id, this.pageMedia);

                this.onImageSelected();
            },

            deleteImage() {
                this.detachImageFromMedia(this.computedValue, this.pageMedia);
                this.computedValue = null;
            },

            onShownModal() { /* @overide */
                this.setTerm('');
                this.getImagesList(route(this.imageListRouteName));
            },

            onImageListLoadedSuccess(data) { /* @override MixinContentHasMediaLibrary */
                this.modalImages = data;
            },

            onImageListLoadedFail(error) { /* @override MixinContentHasMediaLibrary */
                this.closeModal();
            },

            onImageSelected() { /* @override MixinContentHasMediaLibrary */
                this.closeModal();
            },

            onImageUpdated() { /* @override MixinContentHasMediaLibrary */
                this.closeModal();
            },
        },
    };
</script>
