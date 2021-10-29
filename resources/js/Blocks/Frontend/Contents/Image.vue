<template>
    <div>
        <sdb-image
            v-if="hasImage"
            :src="imageSrc"
            :alt="altText"
            :ratio="this.config?.image?.ratio"
            :rounded="this.config?.image?.rounded"
            :square="this.config?.image?.fixedSquare"
        >
        </sdb-image>
    </div>
</template>

<script>
    import MixinContentHasImage from '@/Mixins/ContentHasImage';
    import SdbImage from '@/Sdb/Image';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'Image',
        components: {
            SdbImage,
        },
        mixins: [
            MixinContentHasImage,
        ],
        props: {
            id: String,
            entity: {type: Object, default: {}},
            selectedLocale: String,
        },
        setup(props) {
            return {
                config: props.entity?.config,
            };
        },
        data() {
            return {
                entityImage: this.entity.content.figure.image,
                images: usePage().props.value.images ?? {},
            };
        },
    }
</script>
