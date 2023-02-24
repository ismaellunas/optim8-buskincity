<template>
    <div>
        <div class="columns is-multiline">
            <template
                v-for="(medium, index) in media"
                :key="index"
            >
                <slot
                    :index="index"
                    :thumbnail-url="medium.thumbnail_url ?? medium.file_url"
                    :open-modal="openModal"
                />
            </template>
        </div>

        <biz-modal
            v-if="isActive && activeMedium"
            @close="isActive = false"
        >
            <div class="modal-content">
                <div class="card">
                    <div class="card-image">
                        <figure class="image is-3by2">
                            <img
                                v-if="activeMedium.file_type == 'image'"
                                class="image-source"
                                :src="activeMedium.file_url"
                            >
                            <iframe
                                v-if="activeMedium.file_type == 'video'"
                                class="has-ratio"
                                frameborder="0"
                                :src="activeMedium.file_url"
                                allowfullscreen
                            />
                        </figure>
                    </div>
                    <footer class="card-footer">
                        <div class="column">
                            <div class="is-pulled-left">
                                <a @click.prevent="prev()">
                                    <biz-icon
                                        class="is-medium"
                                        icon="fas fa-lg fa-chevron-circle-left"
                                    />
                                </a>
                            </div>
                            <div class="is-pulled-right has-text-primary">
                                <a @click.prevent="next()">
                                    <biz-icon
                                        class="is-medium"
                                        icon="fas fa-lg fa-chevron-circle-right"
                                    />
                                </a>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </biz-modal>
    </div>
</template>

<script>
    import BizIcon from '@/Biz/Icon.vue';
    import BizModal from '@/Biz/Modal.vue';
    import { nth } from 'lodash';

    export default {
        components: {
            BizIcon,
            BizModal,
        },

        props: {
            media: { type: Array, default: () => [] },
        },

        data() {
            return {
                isActive: false,
                currentIndex: null,
            };
        },

        computed: {
            activeMedium() {
                return nth(this.media, this.currentIndex);
            },

            maxIndex() {
                const length = this.media.length;
                if (length > 0) {
                    return length - 1;
                }
                return null;
            },

            minIndex() {
                return (this.maxIndex != null) ? 0 : null;
            }
        },

        methods: {
            openModal(index) {
                this.isActive = true;
                this.currentIndex = index;
            },

            prev() {
                const previousIndex = this.currentIndex - 1;

                this.currentIndex = (previousIndex >= 0)
                    ? previousIndex
                    : this.maxIndex;
            },

            next() {
                const nextIndex = this.currentIndex + 1;

                this.currentIndex = (nextIndex <= this.maxIndex)
                    ? nextIndex
                    : this.minIndex;
            },
        },
    };
</script>
