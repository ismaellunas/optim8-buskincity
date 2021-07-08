<template>
    <ul class="alert alert-danger" v-if="errors && Object.keys(errors).length > 0">
        <li v-for="error in errors">{{ error }}</li>
    </ul>

    <div class="alert alert-success" v-if="$page.props.flash.message">
        {{ $page.props.flash.message }}
    </div>
</template>

<script>
    export default {
        props: ["errors"]
    }
</script>
