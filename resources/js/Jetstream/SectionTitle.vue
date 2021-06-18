<template>
    <div class="container py-4">
        <h1 class="is-size-4 has-text-weight-semibold">
            <slot name="title"></slot>
        </h1>

        <h2 class="is-size-6">
            <slot name="description"></slot>
        </h2>

        <div class="px-4 px-0-mobile">
            <slot name="aside"></slot>
        </div>
    </div>
</template>
