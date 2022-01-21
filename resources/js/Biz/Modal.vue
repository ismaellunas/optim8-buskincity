<template>
    <div class="modal is-active">
        <div class="modal-background" />
        <div
            class="modal-content"
            :class="contentClass"
        >
            <slot />
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
        emits: ['close'],
        props: {
            contentClass: [Array, Object, String],
            isCloseHidden: {type: Boolean, default: false},
        },
    }
</script>
