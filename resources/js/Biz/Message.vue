<template>
    <article
        class="message"
        :class="type"
    >
        <div
            v-if="hasHeader"
            class="message-header"
        >
            <p>
                <slot name="header" />
            </p>
            <button
                v-if="dismissible"
                class="delete"
                aria-label="delete"
                @click="onClose"
            />
        </div>
        <div class="message-body">
            <slot />
        </div>
    </article>
</template>

<script>
    export default {
        name: 'BizMessage',

        props: {
            type: {
                type: String,
                default: 'is-info',
                validator: (value) => [
                    'is-primary',
                    'is-link',
                    'is-info',
                    'is-success',
                    'is-warning',
                    'is-danger',
                    'is-dark',
                    'is-light',
                ].includes(value),
            },
            dismissible: {
                type: Boolean,
                default: false,
            },
        },

        computed: {
            hasHeader() {
                return !!this.$slots.header;
            },
        },

        methods: {
            onClose() {
                this.$emit('close');
            },
        },
    };
</script>
