<template>
    <div>
        <div class="columns is-multiline">
            <template
                v-for="url in urls"
                :key="url"
            >
                <slot
                    :url="url"
                    :open-modal="openModal"
                />
            </template>
        </div>

        <biz-modal
            v-if="isActive"
            @close="isActive = false"
        >
            <div class="modal-content">
                <div class="card">
                    <div class="card-image">
                        <figure class="image is-3by2">
                            <img
                                class="image-source"
                                :src="activeUrl"
                            >
                        </figure>
                    </div>
                    <footer class="card-footer">
                        <div class="column">
                            <div class="is-pulled-left">
                                <a @click.prevent="prev">
                                    <biz-icon
                                        class="is-medium"
                                        icon="fas fa-lg fa-chevron-circle-left"
                                    />
                                </a>
                            </div>
                            <div class="is-pulled-right has-text-primary">
                                <a @click.prevent="next">
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
    import BizIcon from '@/Biz/Icon';
    import BizModal from '@/Biz/Modal';
    import { nth } from 'lodash';

    export default {
        components: {
            BizIcon,
            BizModal,
        },

        props: {
            urls: {type: Array, default: () => [] },
        },

        data() {
            return {
                activeUrl: null,
                isActive: false,
            };
        },

        computed: {
            currentIndex() {
                return this.urls.indexOf(this.activeUrl);
            },

            maxIndex() {
                const length = this.urls.length;
                if (length > 0) {
                    return length - 1;
                }
                return null;
            }
        },

        methods: {
            openModal(url) {
                this.activeUrl = url;
                this.isActive = true;
            },

            prev() {
                let previousIndex = this.currentIndex - 1;

                if (previousIndex >= 0) {
                    this.activeUrl = nth(this.urls, previousIndex);
                }
            },

            next() {
                let nextIndex = this.currentIndex + 1;

                if (nextIndex <= this.maxIndex) {
                    this.activeUrl = nth(this.urls, nextIndex);
                }
            },
        },
    };
</script>
