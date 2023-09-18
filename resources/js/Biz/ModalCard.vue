<template>
    <div
        :class="['modal', 'is-active']"
    >
        <div
            class="modal-background"
            @click="$emit('close')"
        />

        <div
            class="modal-card"
            :class="contentClass"
        >
            <header class="modal-card-head">
                <slot name="header">
                    <p class="modal-card-title has-text-weight-bold">
                        {{ headerTitle ?? "" }}
                    </p>

                    <button
                        aria-label="close"
                        type="button"
                        class="delete"
                        @click="$emit('close')"
                    />
                </slot>
            </header>

            <section class="modal-card-body">
                <slot />
            </section>

            <footer class="modal-card-foot">
                <slot name="footer" />
            </footer>
        </div>

        <button
            v-show="!isCloseHidden"
            class="modal-close is-large"
            type="button"
            @click="$emit('close')"
        />
    </div>
</template>

<script>
    export default {
        name: 'BizModalCard',

        props: {
            contentClass: {
                type: [Array, Object, String],
                default: () => [],
            },
            isCloseHidden: {
                type: Boolean,
                default: false
            },
            headerTitle: { type: String, default: "" },
        },

        emits: ['close'],
    }
</script>
