<template>
    <transition :name="direction" mode="in-out">
        <div v-show="visibleSlide === index" class="carousel-slide">
            <sdb-image
                v-if="hasImage"
                :alt="altText"
                :ratio="config?.carousel?.ratio"
                :src="imageSrc"
            >
                <sdb-button
                    v-if="isEditMode"
                    class="is-small is-overlay"
                    style="z-index: 1"
                    type="button"
                    @click="toggleEdit"
                >
                    <span class="icon" v-if="isFormDisplayed"><i class="fas fa-times-circle"></i></span>
                    <span class="icon" v-else><i class="fas fa-pen"></i></span>
                </sdb-button>
            </sdb-image>
            <div
                v-if="isFormDisplayed"
                class="card-content has-background-info-light is-flex is-justify-content-center is-align-items-center card-custom"
            >
                <sdb-button
                    v-if="isEditMode"
                    class="is-small is-overlay"
                    style="z-index: 1"
                    type="button"
                    @click="toggleEdit"
                >
                    <span class="icon" v-if="isFormDisplayed"><i class="fas fa-times-circle"></i></span>
                    <span class="icon" v-else><i class="fas fa-pen"></i></span>
                </sdb-button>
                <div class="block has-text-centered">
                    <sdb-button
                        @click="$emit('openModal', index)"
                        type="button"
                        style="z-index: 1"
                    >
                        <span>Open Media</span>
                        <span class="icon is-small">
                            <i class="far fa-image"></i>
                        </span>
                    </sdb-button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
    import MixinContainImageContent from '@/Mixins/ContainImageContent';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinEditModeComponent from '@/Mixins/EditModeComponent';
    import SdbButton from '@/Sdb/Button';
    import SdbImage from '@/Sdb/Image';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { useModelWrapper, isBlank } from '@/Libs/utils';

    export default {
        name: 'CarouselSlide',

        components: {
            SdbButton,
            SdbImage,
            SdbToolbarContent,
        },

        mixins: [
            MixinContainImageContent,
            MixinDeletableContent,
            MixinEditModeComponent,
        ],

        props: {
            config: { type: Object, required: true },
            dataImages: { type: Object, required: true },
            dataMedia: { type: Array },
            direction: { type: String, required: true },
            entityMedia: { type: Object },
            index: { type: Number, default: 0 },
            isEditMode: { type: Boolean },
            selectedLocale: String,
            visibleSlide: { type: Number, default: 0 },
        },

        emits: ['openModal'],

        data() {
            return {
                entityImage: this.entityMedia,
                images: this.dataImages ?? {},
                isFormOpen: false,
            };
        },

        setup(props, { emit }) {
            return {
                entity: useModelWrapper(props, emit),
                pageMedia: useModelWrapper(props, emit, 'dataMedia'),
            };
        },

        computed: {
            isFormDisplayed() {
                return this.isEditMode && (
                    !this.hasImage
                    || (this.isEditMode && this.isFormOpen)
                );
            },
        },

        watch: {
            'entityImage.mediaId': function(from) {
                if (!isBlank(from)) {
                    this.isFormOpen = false;
                }
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