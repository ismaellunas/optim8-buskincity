<template>
    <transition
        :name="direction"
        mode="in-out"
    >
        <div
            v-show="visibleSlide === index"
            class="carousel-slide"
        >
            <biz-image
                v-if="hasImage"
                :alt="altText"
                :ratio="config?.carousel?.ratio"
                :src="imageSrc"
            >
                <biz-button-icon
                    type="button"
                    class="is-small is-overlay"
                    style="z-index: 1"
                    :icon="isFormDisplayed ? iconClear : iconEdit"
                    @click="toggleEdit"
                />
            </biz-image>

            <div
                v-if="isFormDisplayed"
                class="card-content has-background-info-light is-flex is-justify-content-center is-align-items-center card-custom"
            >
                <div class="block has-text-centered">
                    <biz-button-icon
                        icon-class="is-small"
                        type="button"
                        style="z-index: 1"
                        :disabled="!can.media.browse"
                        :icon="iconImage"
                        @click="$emit('open-modal', index)"
                    >
                        <span>Open Media</span>
                    </biz-button-icon>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
    import MixinContentHasMediaLibrary from '@/Mixins/ContentHasMediaLibrary';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizImage from '@/Biz/Image.vue';
    import { useModelWrapper, isBlank } from '@/Libs/utils';
    import { clear as iconClear, edit as iconEdit, image as iconImage } from '@/Libs/icon-class';

    export default {
        name: 'CarouselSlide',

        components: {
            BizButtonIcon,
            BizImage,
        },

        mixins: [
            MixinContentHasMediaLibrary,
            MixinDeletableContent,
        ],

        inject: ['can'],

        props: {
            config: {
                type: Object,
                required: true
            },
            dataImages: {
                type: Object,
                required: true
            },
            dataMedia: {
                type: Array,
                default:() => [],
            },
            direction: {
                type: String,
                required: true
            },
            entityMedia: {
                type: Object,
                required: true,
            },
            index: {
                type: Number,
                default: 0
            },
            selectedLocale: {
                type: String,
                required: true,
            },
            visibleSlide: {
                type: Number,
                default: 0
            },
        },

        emits: ['open-modal'],

        setup(props, { emit }) {
            return {
                entity: useModelWrapper(props, emit),
                pageMedia: useModelWrapper(props, emit, 'dataMedia'),
            };
        },

        data() {
            return {
                entityImage: this.entityMedia,
                images: this.dataImages ?? {},
                isFormOpen: false,
                iconClear,
                iconEdit,
                iconImage,
            };
        },

        computed: {
            isFormDisplayed() {
                return !this.hasImage || this.isFormOpen;
            },
        },

        watch: {
            'entityImage.mediaId': {
                handler: function(from) {
                    if (!isBlank(from)) {
                        this.isFormOpen = false;
                    }
                },
                deep: true,
            },
        },

        methods: {
            toggleEdit() {
                this.isFormOpen = !this.isFormOpen;
            },
        },
    }
</script>

<style scoped>
    .carousel-slide {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    .card-custom {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    .left-enter-active {
        animation: leftInAnimation 0.4s ease-in-out;
    }

    .left-leave-active {
        animation: leftOutAnimation 0.4s ease-in-out;
    }

    @keyframes leftInAnimation {
        from { transform: translateX(100%); }
        to { transform: translateX(0%); }
    }

    @keyframes leftOutAnimation {
        from { transform: translateX(0%); }
        to { transform: translateX(-100%); }
    }

    .right-enter-active {
        animation: rightInAnimation 0.4s ease-in-out;
    }

    .right-leave-active {
        animation: rightOutAnimation 0.4s ease-in-out;
    }

    @keyframes rightInAnimation {
        from { transform: translateX(-100%); }
        to { transform: translateX(0%); }
    }

    @keyframes rightOutAnimation {
        from { transform: translateX(0%); }
        to { transform: translateX(+100%); }
    }
</style>