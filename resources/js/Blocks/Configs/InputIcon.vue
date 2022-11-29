<template>
    <div>
        <biz-form-input-icon
            v-model="icon"
            placeholder="e.g fas fa-square-full"
            :can-remove="true"
            :has-type="true"
            :icon-classes="iconClasses"
            :label="label"
            :is-small="true"
        />
    </div>
</template>

<script>
    import BizFormInputIcon from '@/Biz/Form/InputIcon';
    import fontawesomeAllClasses from '@/Json/fontawesome-all-classes';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'ConfigInputIcon',

        components: {
            BizFormInputIcon,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: [String, null], default: "" },
        },

        setup(props, { emit }) {
            return {
                iconClasses: fontawesomeAllClasses,
                icon: useModelWrapper(props, emit),
            };
        },
    }
</script>