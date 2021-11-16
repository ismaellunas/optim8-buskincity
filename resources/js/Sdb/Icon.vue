<template>
    <sdb-field class="has-addons mb-0">
        <div class="control">
            <sdb-button
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
            </sdb-button>
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
            <sdb-button
                type="button"
                @click="$emit('show-modal')"
            >
                Find Icon
            </sdb-button>
        </div>
    </sdb-field>
</template>

<script>
    import SdbButton from '@/Sdb/Button';
    import SdbField from '@/Sdb/Field';

    export default {
        name: 'SdbIcon',

        components: {
            SdbButton,
            SdbField,
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
