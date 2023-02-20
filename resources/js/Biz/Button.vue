<template>
    <component
        :is="tag"
        class="button"
        :type="type"
    >
        <slot />
    </component>
</template>

<script>
    export default {
        name: 'BizButton',
        props: {
            type: { type: String, default: 'submit' },
            tag: { type: String, default: 'button'},
        }
    }
</script>
