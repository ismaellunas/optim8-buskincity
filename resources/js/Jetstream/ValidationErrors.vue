<template>
    <div v-if="hasErrors">
        <div class="notification is-warning">
            <div class="">Whoops! Something went wrong.</div>

            <ul class="">
                <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        computed: {
            errors() {
                return this.$page.props.errors
            },

            hasErrors() {
                return Object.keys(this.errors).length > 0;
            },
        }
    }
</script>
