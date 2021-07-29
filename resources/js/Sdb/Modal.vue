<template>
    <div>
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
    </div>
</template>

<script>
    export default {
        emits: ['close'],
        props: {
            contentClass: [String, Array],
            isCloseHidden: {type: Boolean, default: false},
        },
    }
</script>
