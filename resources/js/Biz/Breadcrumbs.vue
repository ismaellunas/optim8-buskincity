<template>
    <nav
        class="breadcrumb"
        aria-label="breadcrumbs"
    >
        <ul>
            <template
                v-for="(breadcrumb, index) in breadcrumbs"
                :key="index"
            >
                <li v-if="breadcrumb.url">
                    <biz-link
                        v-if="breadcrumb.url"
                        :href="breadcrumb.url"
                    >
                        {{ breadcrumb.title }}
                    </biz-link>
                </li>

                <li
                    v-else
                    class="is-active"
                >
                    <biz-link
                        aria-current="page"
                        href="#"
                    >
                        {{ breadcrumb.title }}
                    </biz-link>
                </li>
            </template>
        </ul>
    </nav>
</template>

<script>
    import BizLink from '@/Biz/Link.vue';

    export default {
        name: "BizBreadcrumbs",

        components: {
            BizLink,
        },

        props: {
            breadcrumbs: { type: Array, required: true },
        },
    };
</script>
