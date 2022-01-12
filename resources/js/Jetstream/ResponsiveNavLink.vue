<template>
    <div>
        <button :class="classes" class="button is-white" v-if="as == 'button'">
            <slot></slot>
        </button>

        <biz-link :href="href" :class="classes" v-else>
            <slot></slot>
        </biz-link>
    </div>
</template>

<script>
    import BizLink from '@/Biz/Link';

    export default {
        components: {
            BizLink,
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
