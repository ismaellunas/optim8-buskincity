<template>
    <div>
        <button :class="classes" class="button is-white" v-if="as == 'button'">
            <slot></slot>
        </button>

        <inertia-link :href="href" :class="classes" v-else>
            <slot></slot>
        </inertia-link>
    </div>
</template>

<script>
    export default {
        props: ['active', 'href', 'as'],

        computed: {
            classes() {
                return this.active
                    ? 'navbar-item is-active'
                    : 'navbar-item'
            }
        }
    }
</script>
