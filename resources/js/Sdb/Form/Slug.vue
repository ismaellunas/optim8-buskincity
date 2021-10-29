<template>
    <sdb-form-input-addons
        v-model="slug"
        :disabled="isSlugDisabled || disabled"
        :label="label"
        :message="message"
        :required="required"
        placeholder="e.g. a-good-news"
        @input="$emit('update:modelValue', $event.target.value)"
        @on-keypress="keyPressSlug"
    >
        <template v-slot:afterInput>
            <div class="control">
                <sdb-button-icon
                    v-show="isSlugDisabled"
                    icon="fas fa-pen"
                    type="button"
                    @click="isSlugDisabled = false"
                />
                <sdb-button-icon
                    v-show="!isSlugDisabled"
                    icon="fas fa-ban"
                    type="button"
                    @click="isSlugDisabled = true"
                />
            </div>
        </template>
    </sdb-form-input-addons>
</template>

<script>
    import SdbFormInputAddons from '@/Sdb/Form/InputAddons';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import { useModelWrapper, regexSlug } from '@/Libs/utils';

    export default {
        name: 'SdbFormSlug',

        components: {
            SdbFormInputAddons,
            SdbButtonIcon,
        },

        props: {
            disabled: {type: Boolean, default: false},
            label: {type: String, default: null},
            message: {type: String, default: null},
            modelValue: {required: true},
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