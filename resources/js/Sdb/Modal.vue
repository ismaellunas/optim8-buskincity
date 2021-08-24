<template>
    <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-content" :class="contentClass">
            <slot></slot>
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
