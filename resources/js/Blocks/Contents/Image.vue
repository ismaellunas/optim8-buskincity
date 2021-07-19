<template>
    <div>
        <sdb-toolbar-content
            v-if="isEditMode"
            :can-edit="canEdit"
            @edit-content="toggleEdit"
            @delete-content="deleteContent"
        />

        <div class="edit-mode-toolbar-content" v-if="isEditMode">
            <div class="field has-addons is-pulled-right">
                <p class="control" v-if="canEdit">
                    <sdb-button type="button" class="is-small" @click="toggleEdit">
                        <span class="icon">
                            <i class="fas fa-pen"></i>
                        </span>
                    </sdb-button>
                </p>
                <p class="control">
                    <sdb-button type="button" class="is-small" @click="deleteContent">
                        <span class="icon">
                            <i class="fas fa-trash"></i>
                        </span>
                    </sdb-button>
                </p>
                <p class="control">
                    <sdb-button type="button" class="is-small handle-content">
                        <span class="icon">
                            <i class="fas fa-arrows-alt"></i>
                        </span>
                    </sdb-button>
                </p>
            </div>
        </div>

        <figure class="image" :class="{'edit-mode-content': isEditMode}">
            <img :src="imageSrc" :alt="content.figure.attrs.alt" v-if="hasImage">
        </figure>

        <div class="card-content" v-if="isFormDisplayed">
            <upload-image-content
                :entityId="entityId"
                :uploadRoute="route('admin.media.upload-image')"
                @close-form="closeForm"
                v-model="content.figure.image.src"
            />
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
                return !this.hasImage || (
                    this.isEditMode && this.isFormOpen
                );
            },
            closeForm() {
                this.isFormOpen = false;
            },
        }
    }
</script>
