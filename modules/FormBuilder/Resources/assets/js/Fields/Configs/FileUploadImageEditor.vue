<template>
    <biz-card
        v-if="hasImageType"
        ref="card"
        :is-collapsed="true"
        :is-expanding-on-load="isExpandingOnLoad"
        @on-click-header-card="onClickHeaderCard"
    >
        <template #headerTitle>
            Image Editor
        </template>

        <config-checkbox
            v-model="computedValue.is_image_editor_enabled"
            label="Is Enabled?"
        />

        <hr>

        <label class="label is-size-7">
            Crop Dimensions
        </label>

        <config-number-addons
            v-if="computedValue.hasOwnProperty('media_dimension')"
            v-model="computedValue.media_dimension.width"
            class="mb-2"
            :settings="{ placeholder: 'Width', addons: 'px' }"
        />

        <config-number-addons
            v-if="computedValue.hasOwnProperty('media_dimension')"
            v-model="computedValue.media_dimension.height"
            :settings="{ placeholder: 'Height', addons: 'px' }"
        />

        <p class="help is-info">
            Recomended dimension for image editor
        </p>
    </biz-card>
</template>

<script>
    import BizCard from '@/Biz/Card.vue';
    import ConfigCheckbox from '@/Blocks/Configs/Checkbox.vue';
    import ConfigNumberAddons from '@/Blocks/Configs/NumberAddons.vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'ConfigFileUploadImageEditor',

        components: {
            BizCard,
            ConfigCheckbox,
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