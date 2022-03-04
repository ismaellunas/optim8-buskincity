<template>
    <div
        v-if="hasMessage && isVisible"
        class="notification is-warning"
    >
        <button
            class="delete"
            @click.prevent="isVisible = false"
        />
        <div class="alert alert-success">
            {{ flash.message_expired }}
        </div>
    </div>
</template>

<script>
    import { isBlank } from '@/Libs/utils';

    export default {
        props: {
            flash: Object,
        },
        data() {
            return {
                isVisible: false,
            };
        },
        computed: {
            hasMessage() {
                return (this.flash && !isBlank(this.flash.message_expired));
            },
        },
        watch: {
            flash: {
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
