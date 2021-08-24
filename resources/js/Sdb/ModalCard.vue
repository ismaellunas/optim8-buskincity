<template>
    <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-card" :class="contentClass">
            <header class="modal-card-head">
                <slot name="header"></slot>
            </header>
            <section class="modal-card-body">
                <slot></slot>
            </section>
            <footer class="modal-card-foot">
                <slot name="footer"></slot>
            </footer>
        </div>
        <button
            class="modal-close is-large"
            type="button"
            @click="$emit('close')"
            v-show="!isCloseHidden">
        </button>
    </div>
</template>

<script>
    export default {
        emits: ['close'],
        props: {
            contentClass: [Array, Object, String],
            isCloseHidden: {type: Boolean, default: false},
        },
    }
</script>
