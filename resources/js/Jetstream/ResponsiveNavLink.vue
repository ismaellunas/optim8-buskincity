<template>
    <div>
        <button :class="classes" class="button is-white" v-if="as == 'button'">
            <slot></slot>
        </button>

        <sdb-link :href="href" :class="classes" v-else>
            <slot></slot>
        </sdb-link>
    </div>
</template>

<script>
    import SdbLink from '@/Sdb/Link';

    export default {
        components: {
            SdbLink,
        },

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
