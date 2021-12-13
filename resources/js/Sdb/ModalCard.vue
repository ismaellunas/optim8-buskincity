<template>
    <div class="modal is-active">
        <div class="modal-background" />

        <div
            class="modal-card"
            :class="contentClass"
        >
            <header class="modal-card-head">
                <slot name="header" />
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
        name: 'SdbModalCard',

        props: {
            contentClass: {
                type: [Array, Object, String],
                default: () => [],
            },
            isCloseHidden: {
                type: Boolean,
                default: false
            },
        },

        emits: ['close'],
    }
</script>
