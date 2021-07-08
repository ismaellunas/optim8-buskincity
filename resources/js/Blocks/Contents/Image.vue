<template>
    <figure class="image" :class="{'edit-mode-content': isEditMode}">
        <img :src="imageSrc" :alt="content.figure.attrs.alt">

        <div class="card-content" v-if="isEditMode">
            <sdb-button type="button" @click="isFormOpen = true" v-if="!isFormOpen">
                <i class="fas fa-edit"></i>
            </sdb-button>
            <upload-image-content
                v-else
                :entityId="entityId"
                :uploadRoute="route('pages.upload-image')"
                @close-form="closeForm"
                v-model="content.figure.image.src"
            />
        </div>
        <div class="edit-mode-buttons" v-if="isEditMode">
            <button class="button is-small" type="button" @click="deleteContent">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
    </figure>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import SdbButton from '@/Sdb/Button';
    import UploadImageContent from '@/Blocks/Contents/UploadImage';
    import { useModelWrapper, isBlank } from '@/Libs/utils';

    export default {
        mixins: [
            EditModeContentMixin,
            DeletableContentMixin
        ],
        components: {
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
            closeForm() {
                this.isFormOpen = false;
            }
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
        }
    }
</script>

<style scoped src="../../../css/column-content.css"></style>
