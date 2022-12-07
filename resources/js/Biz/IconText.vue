<template>
    <span class="icon-text">
        <biz-icon
            v-if="iconPosition == 'left'"
            :icon="icon"
        />

        <span>
            <slot />
        </span>

        <biz-icon
            v-if="iconPosition == 'right'"
            :icon="icon"
        />
    </span>
</template>

<script>
    import BizIcon from '@/Biz/Icon';

    export default {
        name: 'BizIconText',

        components: {
            BizIcon,
        },

        props: {
            icon: { type: [Array, Object, String], default: '' },
            iconPosition: { type: String, default: 'left' },
        },
    };
</script>
