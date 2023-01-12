<template>
    <component
        :is="!disabled ? `biz-link` : `button`"
        ref="link"
        class="button"
        :disabled="disabled"
    >
        <slot />
    </component>
</template>

<script>
    import BizLink from '@/Biz/Link';

    export default {
        components: {
            BizLink,
        },

        props: {
            disabled: { type: Boolean, default: false, }
        },

        mounted() {
            if (!this.disabled) {
                this.$refs.link.$el.removeAttribute('disabled');
            }
        },
    }
</script>
