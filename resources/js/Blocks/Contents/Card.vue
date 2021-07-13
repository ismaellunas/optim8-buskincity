<template>
    <div class="card" :class="{'edit-mode-content': isEditMode}">
       <div class="card-image">
            <figure class="image is-4by3">
                <template v-if="hasImage">
                    <img :src="image.src" alt="image.attrs.alt">
                </template>
                <template v-else>
                    <img src="https://bulma.io/images/placeholders/1280x960.png">
                </template>
            </figure>
        </div>
        <div class="card-content" v-if="isEditMode">
            <sdb-button
                @click="isFormOpen = true"
                type="button"
                v-if="!isFormOpen"
            >
                <i class="fas fa-edit"></i>

            </sdb-button>
            <upload-image-content
                v-else
                :uploadRoute="route('admin.media.upload-image')"
                v-model="content.cardImage.figure.image.src"
                @close-form="closeForm"
                @uploaded-image="updateImageSource"
            />
        </div>
        <div class="card-content">
            <div class="content">
                <template v-if="isEditMode">
                    <sdb-ckeditor-inline
                        v-model="content.cardContent.content.html"
                        />
                </template>
                <template v-else>
                    <div v-html="content.cardContent.content.html"></div>
                </template>
            </div>
        </div>
        <div class="edit-mode-buttons" v-if="isEditMode">
            <button class="button is-small" type="button" @click="deleteContent">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</template>

<script>
    import SdbButton from '@/Sdb/Button';
    import SdbCkeditorInline from '@/Sdb/CkeditorInline'
    import UploadImageContent from '@/Blocks/Contents/UploadImage';
    import card from '@/ComponentStructures/card'
    import { useModelWrapper, emitModelValue, isBlank } from '@/Libs/utils'

    export default {
        components: {
            SdbButton,
            SdbCkeditorInline,
            UploadImageContent,
        },
        props: {
            id: {},
            isEditMode: {type: Boolean, default: false},
            modelValue: {},
        },
        setup(props, { emit }) {
            return {
                content: useModelWrapper(props, emit),
                image: props.modelValue.cardImage.figure.image,
            };
        },
        data() {
            return {
                defaultData: card,
                isFormOpen: false,
            };
        },
        methods: {
            deleteContent() {
                this.$emit('delete-content', this.id);
            },
            updateImageSource(imagePath) {
                this.content.cardImage.figure.image.src = imagePath;
                emitModelValue(this.$emit, this.content);
            },
            closeForm() {
                this.isFormOpen = false;
            }
        },
        computed: {
            hasImage() {
                return !isBlank(this.content.cardImage.figure.image.src);
            }
        }
    }
</script>

<style scoped src="../../../css/column-content.css"></style>
