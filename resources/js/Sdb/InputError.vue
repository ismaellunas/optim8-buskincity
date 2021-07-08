<template>
    <template v-if="isArrayMessage">
        <p class="help is-danger" v-for="msg in message">{{ msg }}</p>
    </template>
    <template v-else>
        <p class="help is-danger" v-show="message">{{ message }}</p>
    </template>
</template>

<script>
    import { isBlank } from '@/Libs/utils';

    export default {
        props: ['message'],
        computed: {
            isArrayMessage() {
                return (!isBlank(this.message) && Array.isArray(this.message));
            }
        }
    }
</script>
