<template>
    <div
        class="columns"
        :class="wrapperClass"
        :style="dimensionStyle"
    >
        <div
            v-if="isEditMode"
            class="column is-3 p-1"
        >
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
        <div
            v-if="isEditMode"
            class="column is-9 p-1"
        >
            <div class="field has-addons is-pulled-right">
                <p class="control">
                    <biz-button
                        type="button"
                        class="is-small"
                        @click="duplicateBlock"
                    >
                        <span class="icon">
                            <i class="fas fa-copy" />
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
                            <i class="fas fa-trash" />
                        </span>
                    </biz-button>
                </p>
                <p class="control">
                    <biz-button
                        type="button"
                        class="is-small handle-columns"
                    >
                        <span class="icon">
                            <i class="fas fa-arrows-alt" />
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
                :is-edit-mode="isEditMode"
                :selected-locale="selectedLocale"
            />
        </template>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizSelect from '@/Biz/Select';
    import BlockColumn from '@/Blocks/Column';
    import EditModeComponentMixin from '@/Mixins/EditModeComponent';
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinMediaImage from '@/Mixins/MediaImage';
    import { confirm, confirmDelete } from '@/Libs/alert';
    import { createColumn } from '@/Libs/page-builder.js';
    import { useModelWrapper, isEmpty } from '@/Libs/utils';
    import { provide, ref } from 'vue';

    export default {
        components: {
            BizButton,
            BizSelect,
            BlockColumn,
        },

        mixins: [
            EditModeComponentMixin,
            MixinContentHasDimension,
            MixinMediaImage,
        ],

        props: {
            dataEntities: { type: Object, default: () => {} },
            dataMedia: { type: Array, default: () => [] },
            id: { type: String, required: true },
            isEditMode: { type: Boolean, default: false },
            modelValue: { type: Object, required: true },
            selectedLocale: { type: String, required: true },
        },

        emits: [
            'delete-block',
            'duplicate-block',
        ],

        setup(props, { emit }) {
            let dataMedia = useModelWrapper(props, emit, 'dataMedia');

            provide('dataMedia', dataMedia);

            return {
                block: useModelWrapper(props, emit),
                entities: useModelWrapper(props, emit, 'dataEntities'),
                media: dataMedia,
            };
        },

        data() {
            return {
                columnOptions: [1,2,3,4,5,6],
                editModeWrapperClass: ['edit-mode-columns'],
                numberOfColumns: this.block.columns.length,
            };
        },

        computed: {
            wrapperClass() {
                let wrapperClass = [];

                if (this.isEditMode) {
                    wrapperClass = wrapperClass.concat(
                        'edit-mode-columns',
                        'is-multiline',
                        'box',
                        'p-1',
                        'my-1'
                    );
                }

                const configWrapper = this.dataEntity?.config?.wrapper ?? null;

                return wrapperClass.concat(
                    (configWrapper['backgroundColor'] ?? '')
                ).filter(Boolean);
            },

            dataEntity() {
                return this.dataEntities[ this.block.id ] ?? null;
            },

            configDimension() {
                return this.dataEntity?.config?.dimension ?? null;
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
                const blockIds = self.getResourceFromDataObject(self.block, 'id');

                blockIds.forEach(function (blockId) {
                    if (!isEmpty(self.entities[blockId])) {
                        const mediaIds = self.getResourceFromDataObject(
                            self.entities[blockId],
                            'mediaId'
                        );

                        allMediaIds = allMediaIds.concat(mediaIds);
                    }
                });

                return allMediaIds.filter(Boolean);
            },

            getResourceFromDataObject(dataObject, keyName) {
                const resource = [];

                JSON.stringify(dataObject, (key, value) => {
                    if (key === keyName) {
                        resource.push(value);
                    }

                    return value;
                });

                return resource;
            },
        },
    };
</script>

<style scoped>
.edit-mode-buttons {
    display: none;
    position: absolute;
    bottom: 0.5rem;
    right: 0.5rem;
}
.edit-mode-columns {
    position: relative;
}
.edit-mode-columns:hover > .edit-mode-buttons {
    display: block;
}
</style>
