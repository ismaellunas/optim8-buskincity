<template>
    <div>
        <h2 class="title is-4 mt-5">
            <slot name="title" />
        </h2>
        <div class="box is-shadowless">
            <slot name="content" />
        </div>
    </div>
</template>

<script>
    export default {
        name: 'FrontendActionSection',
    };
</script>
