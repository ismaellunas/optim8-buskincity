<template>
    <biz-button>
        <biz-icon
            :class="iconClass"
            :icon="icon"
        />

        <slot />
    </biz-button>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizIcon from '@/Biz/Icon';

    export default {
        name: 'BizButtonIcon',

        components: {
            BizButton,
            BizIcon,
        },

        props: {
            icon: { type: [Array, Object, String], default: '' },
            iconClass: { type: [Array, Object, String], default: '' },
        },
    };
</script>
