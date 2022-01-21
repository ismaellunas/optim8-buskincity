<template>
    <biz-field class="has-addons mb-0">
        <div class="control">
            <biz-button
                type="button"
            >
                <span
                    v-if="isDisplayIcon"
                    class="icon"
                >
                    <i :class="icon" />
                </span>
                <span
                    v-else
                    class="icon"
                />
            </biz-button>
        </div>

        <div class="control is-expanded">
            <input
                ref="input"
                v-bind="$attrs"
                class="input"
                type="text"
                :class="{'is-danger' : hasError}"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
            >
        </div>

        <div class="control">
            <biz-button
                type="button"
                @click="$emit('show-modal')"
            >
                Find Icon
            </biz-button>
        </div>
    </biz-field>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizField from '@/Biz/Field';

    export default {
        name: 'BizInputIcon',

        components: {
            BizButton,
            BizField,
        },

        props: {
            hasError: {
                type: Boolean,
                default: false
            },
            modelValue: {
                type: [String, null],
                required: true,
            },
        },

        emits: [
            'show-modal',
            'update:modelValue',
        ],

        data() {
            return {
                isDisplayIcon: true,
            };
        },

        computed: {
            icon() {
                return this.modelValue ?? '';
            },
        },

        watch: {
            // To rerender the icon class
            modelValue() {
                this.isDisplayIcon = false;
                setTimeout(() => {
                    this.isDisplayIcon = true;
                }, 200);
            },
        },

        methods: {
            focus() {
                this.$refs.input.focus()
            },
        },
    }
</script>
