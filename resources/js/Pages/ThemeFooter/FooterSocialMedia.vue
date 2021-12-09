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
                <nav class="panel">
                    <div class="panel-block p-4 has-text-weight-bold">
                        Social Media Items
                    </div>
                    <div
                        v-for="(socialMedia, index) in socialMediaMenus"
                        :key="index"
                        class="panel-block p-4"
                    >
                        <div
                            class="level"
                            style="width:100%"
                        >
                            <div class="level-left">
                                <span class="panel-icon">
                                    <i
                                        :class="socialMedia.icon"
                                        aria-hidden="true"
                                    />
                                </span>
                                {{ socialMedia.url ?? "-" }}
                            </div>
                            <div class="level-right">
                                <sdb-button
                                    class="is-ghost has-text-black"
                                    @click.prevent="openFormModal(socialMedia, index)"
                                >
                                    <span class="icon is-small">
                                        <i class="fas fa-pen" />
                                    </span>
                                </sdb-button>
                                <sdb-button
                                    class="is-ghost has-text-black ml-1"
                                    @click.prevent="deleteSocialMedia(index)"
                                >
                                    <span class="icon is-small">
                                        <i class="far fa-trash-alt" />
                                    </span>
                                </sdb-button>
                            </div>
                        </div>
                    </div>
                    <a
                        class="panel-block p-4 has-text-link"
                        @click.prevent="openFormModal()"
                    >
                        <span class="panel-icon has-text-link">
                            <i
                                class="fas fa-plus"
                                aria-hidden="true"
                            />
                        </span>
                        Add social media
                    </a>
                </nav>
            </div>
        </div>

        <footer-social-media-form
            v-if="isModalOpen"
            :errors="socialMediaErrors"
            :social-media="selectedSocialMedia"
            :selected-index="selectedIndex"
            @add-social-media="addSocialMedia"
            @close="closeModal()"
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