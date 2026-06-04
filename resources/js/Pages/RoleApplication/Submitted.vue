<template>
    <div class="container py-6">
        <div class="box" style="max-width: 640px; margin: 0 auto;">
            <h1 class="title is-4">{{ title }}</h1>
            <p>Thank you. Your application is pending review by an administrator.</p>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            title: { type: String, required: true },
        },
    };
</script>
