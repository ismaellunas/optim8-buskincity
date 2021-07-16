<template>
    <div>
        <button type="submit" class="block px-4 py-2" v-if="as == 'button'">
            <slot></slot>
        </button>
        
        <a :href="href" class="block px-4 py-2" v-else-if="as =='a'">
            <slot></slot>
        </a>

        <sdb-link :href="href" class="block px-4 py-2" v-else>
            <slot></slot>
        </sdb-link>
    </div>
</template>

<script>
    import SdbLink from '@/Sdb/Link';

    export default {
        components: { SdbLink },
        props: ['href', 'as']
    }
</script>
