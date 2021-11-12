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
                            v-for="(link, index) in links"
                            :key="index"
                            class="column"
                        >
                            <sdb-image
                                class="is-48x48 image-pointer"
                                rounded="is-rounded"
                                :src="link.image_url"
                                @click.prevent="openFormModal(link, index)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer-link-form
            v-if="isModalOpen"
            :social-media="selectedLink"
            :selected-index="selectedIndex"
            @add-social-media="addSocialMedia"
            @close="closeModal()"
            @delete-link="deleteLink"
        />
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import SdbImage from '@/Sdb/Image';
    import FooterLinkForm from './FooterLinkForm';
    import { useModelWrapper } from '@/Libs/utils';
    import { confirmDelete } from '@/Libs/alert';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'FooterLink',

        components: {
            SdbButton,
            SdbImage,
            FooterLinkForm,
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
                links: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                selectedLink: {},
                selectedIndex: null,
            };
        },

        methods: {
            openFormModal(link = null, index = null) {
                this.selectedLink = link ?? {};
                this.selectedIndex = index ?? null;
                this.isModalOpen = true;
            },

            addSocialMedia(link) {
                this.links.push(
                    cloneDeep(link)
                );
            },

            deleteLink(index) {
                confirmDelete("Are you sure?").then((result) => {
                    if (result.isConfirmed) {
                        this.links.splice(index, 1);
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