<template>
    <biz-form-input-addons
        v-model="computedValue"
        :disabled="isKeyDisabled || disabled"
        :label="label"
        :message="message"
        :required="required"
        :placeholder="placeholder"
        :is-small="isSmall"
        @input="$emit('update:modelValue', $event.target.value)"
        @on-keypress="onKeypress"
    >
        <template #afterInput>
            <div class="control">
                <biz-button-icon
                    v-show="isKeyDisabled"
                    :icon="icon.edit"
                    type="button"
                    :class="{'is-small': isSmall}"
                    tabindex="-1"
                    @click="isKeyDisabled = false"
                />
                <biz-button-icon
                    v-show="!isKeyDisabled"
                    :icon="icon.suspend"
                    type="button"
                    :class="{'is-small': isSmall}"
                    tabindex="-1"
                    @click="isKeyDisabled = true"
                />
            </div>
        </template>

        <template #note>
            <slot name="note" />
        </template>
    </biz-form-input-addons>
</template>

<script>
    import BizFormInputAddons from '@/Biz/Form/InputAddons.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import icon from '@/Libs/icon-class';

    export default {
        name: 'BizFormKey',

        components: {
            BizFormInputAddons,
            BizButtonIcon,
        },

        props: {
            disabled: {type: Boolean, default: false},
            label: {type: String, default: null},
            message: {type: [Array, Object, String], default: undefined},
            modelValue: {type: [String, null], required: true},
            required: {type: Boolean, default: true},
            placeholder: {type: String, default: 'e.g. a_good_news'},
            isSmall: {type: Boolean, default: false},
        },

        emits: [
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                isKeyDisabled: true,
                icon,
            };
        },

        methods: {
            onKeypress(event) {
                let char = String.fromCharCode(event.keyCode);
                const lastCharacter = event.target.value.slice(-1);
                const regexKey = "a-z0-9\_";

                if ( (char === ' ' || char === '-') && (lastCharacter !== '_')) {
                    event.target.value += '_';
                } else if (char === '_' && lastCharacter === '_') {
                    event.target.value += '';
                } else if ((new RegExp('^['+regexKey+']+$')).test(char)) {
                    return true;
                }

                event.preventDefault();
            },
        },
    }
</script>