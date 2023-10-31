<template>
    <biz-button>
        <biz-icon
            :class="iconClass"
            :icon="icon"
        />

        <slot>
            <span v-if="label">{{ label }}</span>
        </slot>
    </biz-button>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizIcon from '@/Biz/Icon.vue';

    export default {
        name: 'BizButtonIcon',

        components: {
            BizButton,
            BizIcon,
        },

        props: {
            icon: { type: [Array, Object, String], default: '' },
            iconClass: { type: [Array, Object, String], default: '' },
            label: { type: [String, Number, null], default: '' },
        },
    };
</script>
