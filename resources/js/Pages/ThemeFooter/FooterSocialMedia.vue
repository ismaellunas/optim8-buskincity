<template>
    <div>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <span class="has-text-weight-bold">
                        {{ i18n.social_media }}
                    </span>

                    <biz-tooltip
                        class="ml-1"
                        :message="i18n.tips.social_media"
                    />
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <draggable
                    class="has-background-light p-2"
                    handle=".handle-social-media"
                    item-key="id"
                    tag="div"
                    :animation="300"
                    :group="{ name: 'g1' }"
                    :list="computedSocialMedia"
                >
                    <template #item="{ element, index }">
                        <biz-card
                            class="handle-social-media mb-1"
                            class-card-content="p-2 is-clickable"
                        >
                            <div class="level">
                                <div class="level-left">
                                    <div class="level-item">
                                        <div class="buttons">
                                            <biz-button-icon
                                                type="button"
                                                :icon="icon.up"
                                                :disabled="index == 0"
                                                @click="moveSocialMedia('up', index)"
                                            />

                                            <biz-button-icon
                                                type="button"
                                                :icon="icon.down"
                                                :disabled="index == (computedSocialMedia.length - 1)"
                                                @click="moveSocialMedia('down', index)"
                                            />
                                        </div>
                                    </div>

                                    <div class="level-item">
                                        <biz-icon-text :icon="element.icon">
                                            {{ element.url ?? "-" }}
                                        </biz-icon-text>
                                    </div>
                                </div>

                                <div class="level-right">
                                    <div class="buttons">
                                        <biz-button-icon
                                            :icon="icon.edit"
                                            icon-class="is-small"
                                            type="button"
                                            class="is-ghost has-text-black"
                                            @click.prevent="openFormModal(element, index)"
                                        />

                                        <biz-button-icon
                                            :icon="icon.remove"
                                            icon-class="is-small"
                                            type="button"
                                            class="is-ghost has-text-black"
                                            @click.prevent="deleteSocialMedia(index)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </biz-card>
                    </template>
                </draggable>

                <div class="field mt-4">
                    <div class="control">
                        <a
                            @click.prevent="openFormModal()"
                        >
                            <biz-button-icon
                                type="button"
                                class="is-primary"
                                :icon="icon.add"
                                @click="openFormModal()"
                            >
                                <span>
                                    {{ sentenceCase(i18n.add_social_media) }}
                                </span>
                            </biz-button-icon>
                        </a>
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
            @update-social-media="updateSocialMedia"
        />
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasTranslation from '@/Mixins/HasTranslation';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizCard from '@/Biz/Card.vue';
    import BizIconText from '@/Biz/IconText.vue';
    import BizTooltip from '@/Biz/Tooltip.vue';
    import Draggable from "vuedraggable";
    import FooterSocialMediaForm from './FooterSocialMediaForm.vue';
    import icon from '@/Libs/icon-class';
    import { cloneDeep } from 'lodash';
    import { confirmDelete } from '@/Libs/alert';
    import { sentenceCase } from 'change-case';
    import { useModelWrapper } from '@/Libs/utils';
    import { moveItemUp, moveItemDown } from '@/Libs/menu-builder';

    export default {
        name: 'FooterSocialMedia',

        components: {
            BizButtonIcon,
            BizCard,
            BizIconText,
            BizTooltip,
            Draggable,
            FooterSocialMediaForm,
        },

        mixins: [
            MixinHasModal,
            MixinHasTranslation,
        ],

        props: {
            modelValue: {
                type: Array,
                required: true,
            },
        },

        setup(props, { emit }) {
            return {
                computedSocialMedia: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                icon,
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
                        self.computedSocialMedia.push(
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
                        this.computedSocialMedia.splice(index, 1);
                        this.isModalOpen = false;
                    }
                });
            },

            moveSocialMedia(type, index) {
                switch (type) {
                case 'up':
                    moveItemUp(index, this.computedSocialMedia);
                    break;

                case 'down':
                    moveItemDown(index, this.computedSocialMedia);
                    break;
                }
            },

            sentenceCase,
        },
    }
</script>