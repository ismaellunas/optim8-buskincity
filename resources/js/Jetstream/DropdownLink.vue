<template>
    <div>
        <button type="submit" class="block px-4 py-2" v-if="as == 'button'">
            <slot></slot>
        </button>
        
        <a :href="href" class="block px-4 py-2" v-else-if="as =='a'">
            <slot></slot>
        </a>

        <biz-link :href="href" class="block px-4 py-2" v-else>
            <slot></slot>
        </biz-link>
    </div>
</template>

<script>
    import BizLink from '@/Biz/Link';

    export default {
        components: { BizLink },
        props: ['href', 'as']
    }
</script>
