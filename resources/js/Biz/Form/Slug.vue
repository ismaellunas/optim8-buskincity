<template>
    <biz-form-input-addons
        v-model="slug"
        :disabled="isSlugDisabled || disabled"
        :label="label"
        :message="message"
        :required="required"
        placeholder="e.g. a-good-news"
        @input="$emit('update:modelValue', $event.target.value)"
        @on-keypress="keyPressSlug"
    >
        <template #afterInput>
            <div class="control">
                <biz-button-icon
                    v-show="isSlugDisabled"
                    :icon="icon.edit"
                    type="button"
                    tabindex="-1"
                    @click="isSlugDisabled = false"
                />
                <biz-button-icon
                    v-show="!isSlugDisabled"
                    :icon="icon.suspend"
                    type="button"
                    tabindex="-1"
                    @click="isSlugDisabled = true"
                />
            </div>
        </template>
    </biz-form-input-addons>
</template>

<script>
    import BizFormInputAddons from '@/Biz/Form/InputAddons.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import { useModelWrapper, regexSlug } from '@/Libs/utils';
    import icon from '@/Libs/icon-class';

    export default {
        name: 'BizFormSlug',

        components: {
            BizFormInputAddons,
            BizButtonIcon,
        },

        props: {
            disabled: {type: Boolean, default: false},
            label: {type: String, default: null},
            message: { type: [String, Array], default: undefined },
            modelValue: { type: [String, null], required: true },
            required: {type: Boolean, default: true},
        },

        emits: [
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                slug: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                isSlugDisabled: true,
                icon,
            };
        },

        methods: {
            keyPressSlug(event) {
                // @see https://stackoverflow.com/questions/61938667/vue-js-how-to-allow-an-user-to-type-only-letters-in-an-input-field
                let char = String.fromCharCode(event.keyCode);
                const lastCharacter = event.target.value.slice(-1);

                if ( (char === ' ' || char === '_') && (lastCharacter !== '-')) {
                    event.target.value += '-';
                } else if (char === '-' && lastCharacter === '-') {
                    event.target.value += '';
                } else if ((new RegExp('^['+regexSlug+']+$')).test(char)) {
                    return true;
                }
                event.preventDefault();
            },
        },
    }
</script>