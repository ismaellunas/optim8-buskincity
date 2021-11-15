<template>
    <div>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>Social Media</b><br>
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <sdb-button
                        class="is-link is-outlined"
                        @click.prevent="openFormModal()"
                    >
                        Add Social Media
                    </sdb-button>
                </div>
            </div>
            <div class="column">
                <div class="is-pulled-right">
                    <div class="columns">
                        <div
                            v-for="(socialMedia, index) in socialMediaMenus"
                            :key="index"
                            class="column"
                        >
                            <sdb-image
                                class="is-48x48 image-pointer"
                                rounded="is-rounded"
                                :src="imgUrl(socialMedia)"
                                @click.prevent="openFormModal(socialMedia, index)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer-social-media-form
            v-if="isModalOpen"
            :social-media="selectedSocialMedia"
            :selected-index="selectedIndex"
            @add-social-media="addSocialMedia"
            @close="closeModal()"
            @delete-social-media="deleteSocialMedia"
        />
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import SdbImage from '@/Sdb/Image';
    import FooterSocialMediaForm from './FooterSocialMediaForm';
    import { useModelWrapper } from '@/Libs/utils';
    import { confirmDelete } from '@/Libs/alert';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'FooterSocialMedia',

        components: {
            SdbButton,
            SdbImage,
            FooterSocialMediaForm,
        },

        mixins: [
            MixinHasModal,
        ],

        props: {
            modelValue: {
                type: Object,
                required: true,
            },
        },

        setup(props, { emit }) {
            return {
                socialMediaMenus: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                selectedSocialMedia: {},
                selectedIndex: null,
            };
        },

        methods: {
            imgUrl(socialMedia) {
                if (socialMedia.local_url) {
                    return socialMedia.local_url;
                } else if (socialMedia.media?.file_url) {
                    return socialMedia.media?.file_url;
                } else {
                    return "https://dummyimage.com/48x48/e5e5e5/000000.png";
                }
            },

            openFormModal(socialMedia = null, index = null) {
                this.selectedSocialMedia = socialMedia ?? {};
                this.selectedIndex = index ?? null;
                this.isModalOpen = true;
            },

            addSocialMedia(socialMedia) {
                this.socialMediaMenus.push(
                    cloneDeep(socialMedia)
                );
            },

            deleteSocialMedia(index) {
                confirmDelete("Are you sure?").then((result) => {
                    if (result.isConfirmed) {
                        this.socialMediaMenus.splice(index, 1);
                        this.isModalOpen = false;
                    }
                });
            }
        },
    }
</script>

<style scoped>
    .image-pointer {
        cursor: pointer;
    }
</style>