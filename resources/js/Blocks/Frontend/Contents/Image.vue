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
    import MixinGetImageContent from '@/Mixins/GetImageContent';
    import SdbImage from '@/Sdb/Image';
    import { useModelWrapper } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'Image',
        components: {
            SdbImage,
        },
        mixins: [
            MixinGetImageContent,
        ],
        props: {
            id: String,
            modelValue: Object,
            selectedLocale: String,
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue?.config,
                entity: useModelWrapper(props, emit),
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
