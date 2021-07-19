<template>
    <div>
        <figure class="image">
            <img :src="imageSrc" :alt="content.figure.attrs.alt" v-if="hasImage">
            <upload-image-content
                :entityId="entityId"
                :uploadRoute="route('admin.media.upload-image')"
                @close-form="closeForm"
                v-model="content.figure.image.src"
            />
        </figure>

        <div class="card-content has-background-info-light" v-if="isFormDisplayed">
        </div>
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import SdbButton from '@/Sdb/Button';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import UploadImageContent from '@/Blocks/Contents/UploadImage';
    import { useModelWrapper, isBlank } from '@/Libs/utils';

    export default {
        mixins: [
            EditModeContentMixin,
            DeletableContentMixin
        ],
        components: {
            SdbButton,
            SdbToolbarContent,
            UploadImageContent,
            SdbButton,
        },
        props: {
            class: {type: Array},
            id: {},
            entityId: {},
            modelValue: {},
        },
        data() {
            return {
                isFormOpen: false,
            };
        },
        setup(props, { emit }) {
            return {
                content: useModelWrapper(props, emit),
                contentClass: props.modelValue.figure.attrs.class ?? [],
            };
        },
        methods: {
            toggleEdit() {
                this.isFormOpen = !this.isFormOpen;
            },
        },
        computed: {
            hasImage() {
                return !isBlank(this.content.figure.image.src);
            },
            imageSrc() {
                if (this.hasImage) {
                    return this.content.figure.image.src;
                }
                return 'https://bulma.io/images/placeholders/640x480.png';
            },
            /* @overide */
            canEdit() {
                return this.isEditMode && this.hasImage;
            },
            isFormDisplayed() {
                return this.isEditMode && (
                    !this.hasImage
                    || (this.isEditMode && this.isFormOpen)
                );
            },
            closeForm() {
                this.isFormOpen = false;
            },
        }
    }
</script>
