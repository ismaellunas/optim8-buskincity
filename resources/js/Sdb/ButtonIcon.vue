<template>
    <sdb-button>
        <span
            class="icon"
            :class="iconClass"
        >
            <i :class="icon"></i>
        </span>
        <slot></slot>
    </sdb-button>
</template>

<script>
    import SdbButton from '@/Sdb/Button';

    export default {
        components: {
            SdbButton,
        },
        props: [
            'icon',
            'iconClass',
        ],
    }
</script>
