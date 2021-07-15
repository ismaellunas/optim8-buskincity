<template>
    <div class="notification is-info" v-if="hasMessage && isVisible">
        <button class="delete" @click.prevent="isVisible = false"></button>
        <div class="alert alert-success">
            {{ flash.message }}
        </div>
    </div>
</template>

<script>
    import { isBlank } from '@/Libs/utils';

    export default {
        props: {
            flash: Object,
        },
        mounted() {             
            if (this.hasMessage) {
                this.isVisible = true;
            }
        },
        data() {
            return {
                isVisible: false,
            };
        },
        computed: {
            hasMessage() {
                return (this.flash && !isBlank(this.flash.message));
            },
        },
        watch: {
            flash: {
                deep: true,
                handler() {
                    this.isVisible = true;
                }
            }
        }
    }
</script>
