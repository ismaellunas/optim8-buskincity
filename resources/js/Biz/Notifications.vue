<template>
    <div
        v-if="hasMessage && isVisible"
        class="notification is-info"
    >
        <button
            class="delete"
            @click.prevent="isVisible = false"
        />
        <div class="alert">
            {{ message }}
        </div>
    </div>
</template>

<script>
    import { isBlank } from '@/Libs/utils';

    export default {
        name: 'Notification',

        props: {
            message: {type: String, default: null},
        },

        data() {
            return {
                isVisible: false,
            };
        },

        computed: {
            hasMessage() {
                return !isBlank(this.message);
            },
        },

        watch: {
            message: {
                deep: true,
                handler() {
                    this.isVisible = true;
                }
            }
        },

        mounted() {
            if (this.hasMessage) {
                this.isVisible = true;
            }
        },
    }
</script>
