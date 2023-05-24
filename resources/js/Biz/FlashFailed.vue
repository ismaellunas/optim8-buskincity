<template>
    <div
        v-if="hasMessage && isVisible"
        class="notification is-danger"
    >
        <button
            class="delete"
            @click.prevent="isVisible = false"
        />
        <div class="alert alert-success">
            {{ flash.failed }}
        </div>
    </div>
</template>

<script>
    import { isBlank } from '@/Libs/utils';

    export default {
        name: 'BizFlashFailed',
        props: {
            flash: { type: Object, required: true },
        },
        data() {
            return {
                isVisible: false,
            };
        },
        computed: {
            hasMessage() {
                return (this.flash && !isBlank(this.flash.failed));
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
