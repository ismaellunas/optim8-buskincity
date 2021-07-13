<template>
    <inertia-link class="button" :class="class">
        <slot></slot>
    </inertia-link>
</template>

<script>
    export default {
        props: ['class'],
    }
</script>
