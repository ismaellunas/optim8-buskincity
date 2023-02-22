<template>
    <vue-slider
        v-model="computedValue"
        :disabled="disabled"
    />
</template>

<script>
    import VueSlider from 'vue-slider-component';
    import 'vue-slider-component/theme/default.css';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizSlider',

        components: {
            VueSlider
        },

        props: {
            modelValue: { type: [Number, String, null], required: true },
            disabled: { type: Boolean, default: false },
        },

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    };
</script>