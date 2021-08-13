<template>
    <div>
        <div class="columns">
            <div class="column is-full">
                <div class="card">
                    <div class="card-content p-2">
                        <div class="content">
                            <sdb-form-upload-media
                                v-model="form.src"
                                :upload-route="uploadRoute"
                                @on-media-upload-success="$emit('on-media-upload-success', $event)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="columns">
            <div class="column is-three-quarters">
                <div class="columns is-multiline">
                    <div
                        v-for="media in records?.data"
                        class="column"
                        :class="itemClass"
                    >
                        <div
                            class="card"
                            :class="{'has-background-primary-light': (media.id == selectedMediaId)}"
                            @click="onMediaClick(media)"
                        >
                            <div class="card-image px-2 pt-2">
                                <figure v-if="isImage(media)">
                                    <img
                                        :src="media._thumbnail_url"
                                        :alt="media.file_name"
                                    />
                                </figure>
                            </div>

                            <div class="card-content p-2">
                                <div class="content" style="overflow: hidden;">
                                    <p>{{ media.file_name }}</p>
                                </div>
                            </div>

                            <footer class="card-footer">

                                <sdb-button
                                    v-if="isImage(media)"
                                    class="card-footer-item p-2 is-borderless is-shadowless is-info is-inverted"
                                    title="Preview"
                                    @click="previewImage(media)"
                                >
                                    <span class="icon is-small">
                                        <i class="fas fa-expand"></i>
                                    </span>
                                </sdb-button>

                                <slot name="actions" :media="media"></slot>
                            </footer>
                        </div>
                    </div>

                    <div class="column is-full" v-if="isPaginationDisplayed">
                        <sdb-pagination
                            :links="records?.links ?? []"
                        />
                    </div>
                </div>
            </div>
            <div class="column">
                <div
                    v-if="selectedMedia"
                    class="card has-background-primary-light"
                >
                    <header class="card-header">
                        <p class="card-header-title">Detail</p>
                    </header>
                    <div class="card-image p-2">
                        <figure class="image">
                            <img :src="selectedMedia._thumbnail_url ?? ''" alt="">
                        </figure>
                    </div>
                    <div class="card-content">
                        <div class="content">
                            <sdb-form-input
                                label="Name"
                                v-model="selectedMedia.file_name"
                                :message="error('file_name')"
                                :disabled="true"
                                required
                            />
                            <sdb-form-input
                                label="Size"
                                v-model="selectedMedia.size"
                                :message="error('size')"
                                :disabled="true"
                                required
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <sdb-modal
            v-show="isModalOpen"
            @close="closeModal()"
        >
            <p class="image">
                <img :src="previewImageSrc" alt="">
            </p>
        </sdb-modal>
    </div>
</template>

<script>
    import HasPageErrors from '@/Mixins/HasPageErrors';
    import HasModalMixin from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormUploadMedia from '@/Sdb/Form/UploadMedia';
    import SdbInputFile from '@/Sdb/InputFile';
    import SdbModal from '@/Sdb/Modal';
    import SdbPagination from '@/Sdb/Pagination';
    import { isEmpty } from 'lodash';
    import { reactive, ref } from "vue";
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        mixins: [
            HasPageErrors,
            HasModalMixin,
        ],
        components: {
            SdbButton,
            SdbFormInput,
            SdbFormUploadMedia,
            SdbInputFile,
            SdbModal,
            SdbPagination,
        },
        props: {
            isAjax: {type: Boolean, default: false},
            isPaginationDisplayed: {type: Boolean, default: true},
            itemClass: {default: ['is-3']},
            records: {},
            baseRouteName: {},
            uploadRoute: String,
        },
        setup(props) {
            const form = useForm({
                name: null,
                file: null,
            })

            const submitRoute = route('admin.media.store');

            function submit() {
                form.post(submitRoute);
            };

            return {
                selectedMediaId: ref(null),
                form,
                submit,
            };
        },
        data() {
            return {
                previewImageSrc: null,
            };
        },
        methods: {
            isImage(media) {
                return media.file_type === "image";
            },
            onMediaClick(media) {
                this.selectedMediaId = media.id;
            },
            editMedia(media) { /* TODO : remove or fill the method */
                if (this.isImage(media)) {
                    console.log('edit-media');
                }
            },
            previewImage(media) {
                this.previewImageSrc = media.file_url;
                this.openModal();
            },
        },
        computed: {
            selectedMedia() {
                if (isEmpty(this.records.data)) {
                    return null;
                }

                if (this.selectedMediaId === null) {
                    return this.records.data[0];
                }

                const self = this;
                let selectedMedia = this.records.data.find((media) => {
                    return media.id == self.selectedMediaId;
                });

                if (isEmpty(selectedMedia)) {
                    selectedMedia = this.records.data[0];
                }

                return selectedMedia;
            },
        },
    }
</script>
