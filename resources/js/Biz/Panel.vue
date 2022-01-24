<template>
    <article class="panel">
        <p class="panel-heading">
            <slot name="heading" />
        </p>
        <slot />
    </article>
</template>

<script>
    export default {
        name: 'BizPanel',
    }
</script>