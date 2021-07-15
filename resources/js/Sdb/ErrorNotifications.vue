<template>
    <div class="notification is-danger" v-if="isVisible && hasError">
        <button class="delete" @click.prevent="isVisible = false"></button>
        <ul class="alert alert-danger">
            <template v-for="error in errors">
                <li v-for="message in error">
                    {{ message[0] }}
                </li>
            </template>
        </ul>
    </div>
</template>

<script>
    export default {
        props: {
            errors: Object,
        },
        mounted() {             
            if (this.hasError) {
                this.isVisible = true;
            }
        },
        data() {
            return {
                isVisible: false,
            };
        },
        computed: {
            hasError() {
                return (this.errors && Object.keys(this.errors).length > 0);
            },
        },
        watch: {
            errors: {
                deep: true,
                handler() {
                    this.isVisible = true;
                }
            }
        }
    }
</script>
