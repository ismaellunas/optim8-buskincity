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
                            <span
                                class="icon icon-pointer has-background-grey-lighter p-5"
                                @click.prevent="openFormModal(socialMedia, index)"
                            >
                                <i
                                    class="fa-2x"
                                    :class="socialMedia.icon"
                                />
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer-social-media-form
            v-if="isModalOpen"
            :errors="socialMediaErrors"
            :social-media="selectedSocialMedia"
            :selected-index="selectedIndex"
            @add-social-media="addSocialMedia"
            @close="closeModal()"
            @delete-social-media="deleteSocialMedia"
            @update-social-media="updateSocialMedia"
        />
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import FooterSocialMediaForm from './FooterSocialMediaForm';
    import { useModelWrapper } from '@/Libs/utils';
    import { confirmDelete } from '@/Libs/alert';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'FooterSocialMedia',

        components: {
            SdbButton,
            FooterSocialMediaForm,
        },

        mixins: [
            MixinHasModal,
        ],

        props: {
            modelValue: {
                type: Array,
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
                selectedIndex: null,
                selectedSocialMedia: {},
                socialMediaErrors: {},
                validationRoute: route('admin.api.theme.footer.social-media.validate'),
            };
        },

        methods: {
            openFormModal(socialMedia = null, index = null) {
                this.selectedSocialMedia = socialMedia ?? {};
                this.selectedIndex = index ?? null;
                this.socialMediaErrors = {};
                this.isModalOpen = true;
            },

            addSocialMedia(socialMedia) {
                const self = this;
                axios.post(self.validationRoute, socialMedia)
                    .then(() => {
                        self.socialMediaMenus.push(
                            cloneDeep(socialMedia)
                        );
                        self.closeModal();
                    })
                    .catch((error) => {
                        self.socialMediaErrors = error.response.data.errors;
                    });
            },

            updateSocialMedia(socialMedia) {
                const self = this;

                axios.post(self.validationRoute, socialMedia)
                    .then(() => {
                        self.closeModal();
                    })
                    .catch((error) => {
                        self.socialMediaErrors = error.response.data.errors;
                    });
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
    .icon-pointer {
        cursor: pointer;
        border-radius: 100%;
    }
</style>