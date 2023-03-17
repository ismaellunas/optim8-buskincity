<template>
    <inertia-link
        :href="href"
        :class="classes"
    >
        <slot />
    </inertia-link>
</template>

<script>
    import { Link as InertiaLink } from '@inertiajs/vue3'

    export default {
        name: 'JetNavLink',
        components: {InertiaLink},
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
