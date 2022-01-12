<template>
    <li :class="{'is-active': isActive}">
        <slot></slot>
    </li>
</template>

<script>
    export default {
        name: 'BizTabList',
        props: {
            isActive: Boolean,
        },
    };
</script>
