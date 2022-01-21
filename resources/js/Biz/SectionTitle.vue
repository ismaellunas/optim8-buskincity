<template>
    <div class="container py-4">
        <h1 class="is-size-4 has-text-weight-semibold">
            <slot name="title" />
        </h1>

        <h2 class="is-size-6">
            <slot name="description" />
        </h2>

        <div class="px-4 px-0-mobile">
            <slot name="aside" />
        </div>
    </div>
</template>

<script>
    export default {
        name: 'BizSectionTitle',
    }
</script>