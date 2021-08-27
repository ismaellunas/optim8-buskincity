<template>
    <a
        class="button"
        :target="target"
        download
    >
        <slot></slot>
    </a>
</template>

<script>
    export default {
        props: {
            target: {type: String, default: '_blank'},
        }
    }
</script>
