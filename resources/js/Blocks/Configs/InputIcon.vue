<template>
    <div>
        <biz-form-input-icon
            v-model="icon"
            placeholder="e.g fas fa-square-full"
            :has-type="true"
            :icon-classes="iconClasses"
            :label="label"
            :is-small="true"
        />

        <biz-button-icon
            type="button"
            class="is-danger is-small"
            :icon="removeIcon"
            @click="handleRemoveIcon"
        >
            <span>
                Remove
            </span>
        </biz-button-icon>
    </div>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFormInputIcon from '@/Biz/Form/InputIcon.vue';
    import fontawesomeAllClasses from '@/Json/fontawesome-all-classes.json';
    import fontawesomeBrandClasses from '@/Json/fontawesome-brand-classes.json';
    import { useModelWrapper } from '@/Libs/utils';
    import { remove as removeIcon } from '@/Libs/icon-class';

    export default {
        name: 'ConfigInputIcon',

        components: {
            BizButtonIcon,
            BizFormInputIcon,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: [String, null], default: "" },
        },

        setup(props, { emit }) {
            return {
                iconClasses: fontawesomeAllClasses.concat(fontawesomeBrandClasses),
                icon: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                removeIcon,
            };
        },

        methods: {
            handleRemoveIcon() {
                this.icon = null;
            },
        },
    }
</script>