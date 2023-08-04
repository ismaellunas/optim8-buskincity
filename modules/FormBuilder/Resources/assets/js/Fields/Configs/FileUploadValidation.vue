<template>
    <biz-card
        ref="card"
        :is-collapsed="true"
        :is-expanding-on-load="isExpandingOnLoad"
        @on-click-header-card="onClickHeaderCard"
    >
        <template #headerTitle>
            Validation
        </template>

        <config-checkbox
            v-model="computedValue.validation.rules.required"
            label="Is Required?"
        />

        <hr>

        <config-checkboxes
            v-model="computedValue.validation.rules.mimes"
            label="Accepted Type"
            :settings="mimeSettings"
        />

        <hr>

        <config-number-addons
            v-model="computedValue.validation.rules.max"
            label="Maximal File Size"
            :settings="maxSettings"
        />

        <template v-if="hasImageType">
            <hr>

            <label class="label is-size-6">
                Crop Dimensions
            </label>

            <config-number-addons
                v-if="computedValue.hasOwnProperty('image_dimensions')"
                v-model="computedValue.image_dimensions.width"
                label="Width"
                class="mb-2"
                :settings="{ addons: 'px' }"
            />

            <config-number-addons
                v-if="computedValue.hasOwnProperty('image_dimensions')"
                v-model="computedValue.image_dimensions.height"
                label="Height"
                :settings="{ addons: 'px' }"
            />

            <p class="help is-info">
                Recomended dimension for image editor
            </p>
        </template>
    </biz-card>
</template>

<script>
    import BizCard from '@/Biz/Card.vue';
    import ConfigCheckbox from '@/Blocks/Configs/Checkbox.vue';
    import ConfigCheckboxes from '@/Blocks/Configs/Checkboxes.vue';
    import ConfigNumberAddons from '@/Blocks/Configs/NumberAddons.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { maxFileSize as maxFileSizeResponse } from '@/Libs/settings';

    const maxFileSize = await maxFileSizeResponse();

    export default {
        name: 'ConfigFileUploadValidation',

        components: {
            BizCard,
            ConfigCheckbox,
            ConfigCheckboxes,
            ConfigNumberAddons,
        },

        props: {
            modelValue: {type: [Object, Array], default: () => {}},
            isExpandingOnLoad: { type: Boolean, default: false },
            label: { type: String, default: null },
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
                mimeSettings: {
                    options: [
                        { id: 'image', value: 'Image' },
                        { id: 'video', value: 'Video' },
                        { id: 'document', value: 'Document' },
                        { id: 'spreadsheet', value: 'Spreadsheet' },
                        { id: 'presentation', value: 'Presentation' },
                    ],
                },
                maxSettings: {
                    addons: 'KiB',
                    max: maxFileSize,
                    note: `Max file size: ${maxFileSize} KiB`
                },
            };
        },

        computed: {
            hasImageType() {
                return this.computedValue?.validation?.rules?.mimes?.includes('image');
            },
        },

        methods: {
            onClickHeaderCard(isContentShown) {
                this.$emit('on-click-header-card', isContentShown);
            },
        },
    };
</script>