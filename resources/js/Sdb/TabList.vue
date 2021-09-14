<template>
    <li :class="{'is-active': isActive}">
        <slot></slot>
    </li>
</template>

<script>
    export default {
        name: 'SdbTabList',
        props: {
            isActive: Boolean,
        },
    };
</script>
