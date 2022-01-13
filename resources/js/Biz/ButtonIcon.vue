<template>
    <biz-button>
        <span
            class="icon"
            :class="iconClass"
        >
            <i :class="icon" />
        </span>
        <slot />
    </biz-button>
</template>

<script>
    import BizButton from '@/Biz/Button';

    export default {
        name: 'BizButtonIcon',
        components: {
            BizButton,
        },
        props: [
            'icon',
            'iconClass',
        ],
    }
</script>
