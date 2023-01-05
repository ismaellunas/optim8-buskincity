<template>
    <biz-card
        ref="card"
        :is-collapsed="true"
        :is-expanding-on-load="isExpandingOnLoad"
        @on-click-header-card="onClickHeaderCard"
    >
        <template #headerTitle>
            Others
        </template>

        <config-checkbox
            v-if="computedValue.hasOwnProperty('is_multiple_upload')"
            v-model="computedValue.is_multiple_upload"
            label="Is Multiple File Upload?"
        />

        <hr>

        <template v-if="isMultipleUpload">
            <config-number
                v-if="computedValue.hasOwnProperty('max_file_number')"
                v-model="computedValue.max_file_number"
                label="Maximal File Number"
            />

            <hr>

            <config-number
                v-if="computedValue.hasOwnProperty('min_file_number')"
                v-model="computedValue.min_file_number"
                label="Minimal File Number"
            />

            <hr>
        </template>

        <config-number-addons
            v-if="computedValue.hasOwnProperty('max_file_size')"
            v-model="computedValue.max_file_size"
            label="Maximal File Size"
            :settings="{ addons: 'MiB', note: 'This is validation when dropping the file on frontend' }"
        />
    </biz-card>
</template>

<script>
    import BizCard from '@/Biz/Card';
    import ConfigCheckbox from '@/Blocks/Configs/Checkbox';
    import ConfigNumber from '@/Blocks/Configs/Number';
    import ConfigNumberAddons from '@/Blocks/Configs/NumberAddons';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'ConfigFileUploadAttribute',

        components: {
            BizCard,
            ConfigCheckbox,
            ConfigNumber,
            ConfigNumberAddons,
        },

        props: {
            modelValue: {type: [Object, Array], default: () => {}},
            isExpandingOnLoad: { type: Boolean, default: false },
        },

        emits: [
            'on-click-header-card',
        ],

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                oldData: {
                    max_file_number: this.computedValue.max_file_number,
                    min_file_number: this.computedValue.min_file_number,
                    max_file_size: this.computedValue.max_file_size,
                }
            };
        },

        computed: {
            isMultipleUpload() {
                return this.computedValue.is_multiple_upload;
            },
        },

        watch: {
            isMultipleUpload(newData, oldData) {
                if (!newData) {
                    this.computedValue.max_file_number = this.oldData.max_file_number;
                    this.computedValue.min_file_number = this.oldData.min_file_number;
                    this.computedValue.max_file_size = this.oldData.max_file_size;
                }
            }
        },

        methods: {
            onClickHeaderCard(isContentShown) {
                this.$emit('on-click-header-card', isContentShown);
            },
        },
    };
</script>
