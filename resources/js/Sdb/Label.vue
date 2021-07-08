<template>
    <label class="label" :class="class"><slot></slot></label>
</template>

<script>
    export default {
        props: {
            class: {default: []}
        }
    }
</script>
