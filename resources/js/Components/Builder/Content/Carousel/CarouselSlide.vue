<template>
    <transition
        :name="direction"
        mode="in-out"
    >
        <div
            v-show="visibleSlide === index"
            class="carousel-slide"
        >
            <sdb-image
                :alt="entityImage.alt"
                :ratio="config?.ratio"
                :src="entityImage.file_url"
            />
        </div>
    </transition>
</template>

<script>
    import SdbImage from '@/Sdb/Image';

    export default {
        name: 'CarouselSlide',

        components: {
            SdbImage,
        },

        props: {
            config: {
                type: Object,
                required: true
            },
            direction: {
                type: String,
                required: true
            },
            entityImage: {
                type: Object,
                default:() => {}
            },
            index: {
                type: Number,
                default: 0
            },
            visibleSlide: {
                type: Number,
                default: 0
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