<template>
    <Link :href="href" :class="classes">
        <slot></slot>
    </Link>
</template>

<script>
    import { Link } from '@inertiajs/inertia-vue3'

    export default {
        components: {Link},
        props: ['href', 'active'],

        computed: {
            classes() {
                return this.active
                    ? 'navbar-item is-active'
                    : 'navbar-item'
            }
        }
    }
</script>
