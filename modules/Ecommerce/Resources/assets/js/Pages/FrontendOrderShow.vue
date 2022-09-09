<template>
    <div>
        Order List
    </div>
</template>

<script>
    import Layout from '@/Layouts/User';

    export default {
        components: {
        },

        layout: Layout,

        props: {
            //baseRouteName: { type: String, required: true },
            //can: { type: Object, required: true },
            //pageQueryParams: { type: Array, default: () => [] },
            //products: { type: Object, required: true },
        },

        setup(props) {
            //const queryParams = merge(
            //    {},
            //    props.pageQueryParams
            //);

            return {
                //queryParams: ref(queryParams),
                //term: ref(props.pageQueryParams?.term ?? null),
            };
        },

        methods: {
        },
    };
</script>
