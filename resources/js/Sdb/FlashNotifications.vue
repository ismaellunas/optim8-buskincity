<template>
    <div class="notification is-info" v-if="flash && isVisible">
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
            if (!isBlank(this.flash.message)) {
                this.isVisible = true;
            }
        },
        data() {
            return {
                isVisible: false,
            };
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
