<template>
    <div
        class="columns"
        :class="wrapperClass"
        :style="wrapperStyle"
    >
        <div class="column is-3 p-1">
            <div class="field has-addons">
                <div class="control is-expanded">
                    <biz-select
                        v-model="numberOfColumns"
                        class="is-fullwidth is-small"
                        @change="onColumnChange"
                    >
                        <option
                            v-for="(columnNumber, index) in columnOptions"
                            :key="index"
                        >
                            {{ columnNumber }}
                        </option>
                    </biz-select>
                </div>
                <div class="control">
                    <biz-button
                        type="button"
                        class="is-static is-small"
                    >
                        Column(s)
                    </biz-button>
                </div>
            </div>
        </div>
        <div class="column is-9 p-1">
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
    import BizSelect from '@/Biz/Select';
    import BlockColumn from '@/Blocks/Column';
    import { confirm, confirmDelete } from '@/Libs/alert';
    import { createColumn } from '@/Libs/page-builder.js';
    import { useModelWrapper, isEmpty, getResourceFromDataObject, isBlank } from '@/Libs/utils';
    import { inject } from "vue";
    import icon from '@/Libs/icon-class';

    export default {
        components: {
            BizButton,
            BizSelect,
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
                columnOptions: [1,2,3,4,5,6],
                icon,
                images: this.dataImages,
                numberOfColumns: this.block.columns.length,
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

            onColumnChange(event) {
                const numberOfColumns = parseInt(event.target.value);
                const originalNumberOfColumns = this.block.columns.length;

                if (numberOfColumns < originalNumberOfColumns) {
                    const confirmText = 'Are you sure you want to decrease the number of column?';
                    if (confirm(confirmText) === false) {
                        const previousIndex = this.columnOptions.indexOf(originalNumberOfColumns);
                        event.target.selectedIndex = previousIndex;
                        this.numberOfColumns = originalNumberOfColumns;
                        return;
                    }

                    const decreaseNumber = originalNumberOfColumns - numberOfColumns;
                    for (let i = 0; i < decreaseNumber; i++) {
                        this.block.columns.pop();
                    }
                } else {
                    const increaseNumber = numberOfColumns - originalNumberOfColumns;
                    for (let i = 0; i < increaseNumber; i++) {
                        this.block.columns.push(createColumn());
                    }
                }
                this.numberOfColumns = numberOfColumns;
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
        },
    };
</script>
