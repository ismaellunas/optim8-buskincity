<template>
    <biz-card
        ref="card"
        class="component-configurable"
        :is-collapsed="true"
        :is-expanding-on-load="isExpandingOnLoad"
        @on-click-header-card="onClickHeaderCard"
    >
        <template #headerTitle>
            Columns
        </template>

        <biz-form-select
            v-model="numberOfColumns"
            :is-fullwidth="true"
            :has-addons="true"
            @change="onColumnChange"
        >
            <option
                v-for="(columnNumber, index) in columnOptions"
                :key="index"
            >
                {{ columnNumber }}
            </option>

            <template #afterInput>
                <div class="control">
                    <biz-button
                        type="button"
                        class="is-static"
                    >
                        Column(s)
                    </biz-button>
                </div>
            </template>
        </biz-form-select>

        <hr>

        <template
            v-for="(column, index) in computedValue"
            :key="index"
        >
            <biz-form-slider
                v-model="column.size"
                :label="`Size of column ${index + 1}`"
                field-class="mb-5"
                :adsorb="true"
                :marks="true"
                :data="sliderData[index]"
            />
        </template>
    </biz-card>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizCard from '@/Biz/Card';
    import BizFormSlider from '@/Biz/Form/Slider';
    import BizFormSelect from '@/Biz/Form/Select';
    import { useModelWrapper } from '@/Libs/utils';
    import { confirm } from '@/Libs/alert';
    import { createColumn } from '@/Libs/page-builder.js';

    export default {
        name: 'ConfigColumns',

        components: {
            BizButton,
            BizCard,
            BizFormSlider,
            BizFormSelect,
        },

        props: {
            modelValue: { type: Array, required: true },
            isExpandingOnLoad: { type: Boolean, default: false },
            structure: { type: Object, default: () => {} },
        },

        emits: [
            'on-click-header-card',
            'update:modelValue',
        ],

        setup(props, {emit}) {
            let computedStructure = useModelWrapper(props, emit, 'structure');

            return {
                computedValue: useModelWrapper(props, emit),
                computedStructure: useModelWrapper(props, emit, 'structure'),
            };
        },

        data() {
            return {
                columnOptions: [1,2,3,4,5,6],
                numberOfColumns: 1,
                sliderData: [],
            };
        },

        watch: {
            computedValue: {
                handler(newValue, oldValue) {
                    let maxColumns = 12;
                    let totalColumn = newValue.length;
                    let totalSize = newValue.reduce((total, obj) => (obj.size != "auto") ? total + parseInt(obj.size) : total,0);
                    let totalAuto = newValue.reduce((total, obj) => (obj.size == "auto") ? total + 1 : total,0);

                    this.sliderData = [];

                    let tmpSize = null;
                    let tmpData = [];
                    for (let i = 0; i < totalColumn; i++) {
                        tmpData = ['auto'];
                        tmpSize = (newValue[i].size == "auto") ? 1 : parseInt(newValue[i].size);

                        for (let sizeIndex = 1; sizeIndex <= tmpSize + (maxColumns - totalSize - totalAuto); sizeIndex++) {
                            tmpData.push(String(sizeIndex));
                        }

                        this.sliderData.push(tmpData);
                    }
                },
                deep: true,
                immediate: true,
            },

            'computedStructure.columns': {
                handler(newValue, oldValue) {
                    this.numberOfColumns = newValue.length;
                },
                immediate: true,
            },
        },

        methods: {
            onClickHeaderCard(isContentShown) {
                this.$emit('on-click-header-card', isContentShown);
            },

            onColumnChange(event) {
                const self = this;
                const numberOfColumns = parseInt(event.target.value);
                const originalNumberOfColumns = self.computedStructure.columns.length;

                if (numberOfColumns < originalNumberOfColumns) {
                    const confirmText = 'Are you sure you want to decrease the number of column?';
                    const config = {
                        customClass: {
                            confirmButton: 'component-configurable',
                            cancelButton: 'component-configurable'
                        },
                    };

                    confirm(confirmText, null, "Yes", config)
                        .then((result) => {
                            if (!result.isConfirmed) {
                                const previousIndex = self.columnOptions.indexOf(originalNumberOfColumns);

                                event.target.selectedIndex = previousIndex;
                                self.numberOfColumns = originalNumberOfColumns;

                                return;
                            } else {
                                const decreaseNumber = originalNumberOfColumns - numberOfColumns;

                                for (let i = 0; i < decreaseNumber; i++) {
                                    self.computedStructure.columns.pop();
                                }

                                self.setDefaultColumnSize();
                            }
                        })
                } else {
                    const increaseNumber = numberOfColumns - originalNumberOfColumns;

                    for (let i = 0; i < increaseNumber; i++) {
                        self.computedStructure.columns.push(createColumn());
                    }

                    self.setDefaultColumnSize();
                }

                self.numberOfColumns = numberOfColumns;
            },

            setDefaultColumnSize()
            {
                let columns = [];

                for (let i = 0; i < this.computedStructure.columns.length; i++) {
                    columns.push({
                        size: 'auto',
                    })
                }

                this.computedValue = columns;
            },
        },
    }
</script>

<style>
    .vue-slider-mark-label {
        font-size: 10px;
    }
</style>