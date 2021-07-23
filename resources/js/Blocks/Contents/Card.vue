<template>
    <div>
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />

        <div class="card">
            <div class="card-image">
                <figure class="image" :class="figure.attrs.class" v-if="hasImage">
                    <img :src="image.src" alt="image.attrs.alt">
                </figure>

                <sdb-button
                    type="button"
                    class="is-overlay is-small"
                    @click.prevent="toggleEdit"
                    v-if="isEditMode && hasImage">
                    <span class="icon">
                        <i class="fas fa-times" v-if="isFormDisplayed"></i>
                        <i class="fas fa-pen" v-else></i>
                    </span>
                </sdb-button>

                <div class="card-content has-background-info-light" v-if="isFormDisplayed">
                    <upload-image-content
                        :uploadRoute="route('admin.media.upload-image')"
                        v-model="content.cardImage.figure.image.src"
                        @uploaded-image="updateImageSource"
                    />
                </div>
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
        </div>
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import SdbButton from '@/Sdb/Button';
    import SdbCkeditorInline from '@/Sdb/CkeditorInline'
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import UploadImageContent from '@/Blocks/Contents/UploadImage';
    import { useModelWrapper, emitModelValue, isBlank } from '@/Libs/utils'

    export default {
        mixins: [
            EditModeContentMixin,
            DeletableContentMixin
        ],
        components: {
            SdbButton,
            SdbCkeditorInline,
            SdbToolbarContent,
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
                figure: props.modelValue.cardImage.figure,
            };
        },
        data() {
            return {
                isFormOpen: false,
            };
        },
        methods: {
            updateImageSource(imagePath) {
                this.content.cardImage.figure.image.src = imagePath;
                emitModelValue(this.$emit, this.content);
            },
            toggleEdit() {
                this.isFormOpen = !(this.isFormOpen);
            },
        },
        computed: {
            hasImage() {
                return !isBlank(this.content.cardImage.figure.image.src);
            },
            isFormDisplayed() {
                return !this.hasImage || (
                    this.hasImage && this.isFormOpen
                );
            }
        }
    }
</script>
