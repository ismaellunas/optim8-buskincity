<template>
    <div
        class="columns"
        :class="wrapperClass"
        :style="wrapperStyle"
    >
        <div class="column is-12 p-1">
            <div class="field has-addons is-pulled-right">
                <p class="control">
                    <biz-button
                        type="button"
                        class="is-small"
                        @click="duplicateBlock"
                    >
                        <span class="icon">
                            <i :class="icon.copy" />
                        </span>
                    </biz-button>
                </p>
                <p class="control">
                    <biz-button
                        type="button"
                        class="is-small"
                        @click="deleteBlock"
                    >
                        <span class="icon">
                            <i :class="icon.remove" />
                        </span>
                    </biz-button>
                </p>
                <p class="control">
                    <biz-button
                        type="button"
                        class="is-small handle-columns"
                    >
                        <span class="icon">
                            <i :class="icon.move" />
                        </span>
                    </biz-button>
                </p>
            </div>
        </div>
        <template
            v-for="(column, index) in block.columns"
            :key="column.id"
        >
            <block-column
                :id="column.id"
                :components="block.columns[index].components"
                :data-entities="entities"
                :selected-locale="selectedLocale"
                :class="sizeClass(configColumns[index]?.size ?? null)"
            />
        </template>
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinContentHasImage from '@/Mixins/ContentHasImage';
    import MixinEditModeComponent from '@/Mixins/EditModeComponent';
    import MixinMediaImage from '@/Mixins/MediaImage';
    import BizButton from '@/Biz/Button';
    import BlockColumn from '@/Blocks/Column';
    import { confirm, confirmDelete } from '@/Libs/alert';
    import { useModelWrapper, isEmpty, getResourceFromDataObject, isBlank } from '@/Libs/utils';
    import { inject } from "vue";
    import icon from '@/Libs/icon-class';

    export default {
        components: {
            BizButton,
            BlockColumn,
        },

        mixins: [
            MixinContentHasDimension,
            MixinContentHasImage,
            MixinEditModeComponent,
            MixinMediaImage,
        ],

        props: {
            dataEntities: { type: Object, default: () => {} },
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
            selectedLocale: { type: String, required: true },
        },

        emits: [
            'delete-block',
            'duplicate-block',
        ],

        setup(props, { emit }) {
            return {
                block: useModelWrapper(props, emit),
                dataImages: inject('dataImages'),
                entities: useModelWrapper(props, emit, 'dataEntities'),
                media: inject('dataMedia'),
            };
        },

        data() {
            return {
                icon,
                images: this.dataImages,
            };
        },

        computed: {
            wrapperClass() {
                let wrapperClass = [];

                wrapperClass = wrapperClass.concat(
                    'edit-mode-columns',
                    'is-multiline',
                    'box',
                    'p-1',
                    'my-1'
                );

                if (this.hasImage) {
                    wrapperClass = wrapperClass.concat(
                        'pb-background-image'
                    );
                }

                const configWrapper = this.entity?.config?.wrapper ?? null;

                return wrapperClass.concat(
                    (configWrapper['backgroundColor'] ?? ''),
                    (configWrapper['rounded'] ?? ''),
                ).filter(Boolean);
            },

            wrapperStyle() {
                let wrapperStyle = [];

                if (this.hasImage) {
                    wrapperStyle.push({
                        'background-image': 'url(' + this.imageSrc + ')',
                    });
                }

                return wrapperStyle.concat(this.dimensionStyle);
            },

            entity() {
                return this.entities[ this.block.id ] ?? null;
            },

            configDimension() {
                return this.entity?.config?.dimension ?? null;
            },

            configColumns() {
                return this.entity?.config?.columns ?? [];
            },
        },

        watch: {
            'entity.config.wrapper.backgroundImage': {
                handler(newValue, oldValue) {
                    this.entityImage.mediaId = newValue;
                },
                deep: true,
                immediate: true
            },
        },

        methods: {
            deleteBlock() {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.onBlockDeleted();

                        self.$emit('delete-block', self.id)
                    }
                })
            },

            duplicateBlock() {
                const self = this;

                confirm(
                    'Duplicate Component?'
                ).then((result) => {
                    if (result.isConfirmed) {
                        self.$emit('duplicate-block', self.id)

                        self.onBlockDuplicated();
                    }
                });
            },

            onBlockDuplicated() {
                const self = this;
                const mediaIds = this.getAllMediaIdsFromBlock();

                mediaIds.forEach(function (mediaId) {
                    if (mediaId) {
                        self.attachImageToMedia(mediaId, self.media);
                    }
                });
            },

            onBlockDeleted() {
                const self = this;
                const mediaIds = this.getAllMediaIdsFromBlock();

                mediaIds.forEach(function (mediaId) {
                    if (mediaId) {
                        self.detachImageFromMedia(mediaId, self.media);
                    }
                });
            },

            getAllMediaIdsFromBlock() {
                const self = this;
                let allMediaIds = [];
                const blockIds = getResourceFromDataObject(self.block, 'id');

                blockIds.forEach(function (blockId) {
                    if (!isEmpty(self.entities[blockId])) {
                        const mediaIds = getResourceFromDataObject(
                            self.entities[blockId],
                            'mediaId'
                        );

                        const backgroundImages = getResourceFromDataObject(
                            self.entities[blockId],
                            'backgroundImage'
                        );

                        allMediaIds = allMediaIds.concat(mediaIds, backgroundImages);
                    }
                });

                return allMediaIds.filter(Boolean);
            },

            sizeClass(size = null) {
                if (!size || size == "auto") {
                    return null;
                }

                return `is-${size}`;
            }
        },
    };
</script>
