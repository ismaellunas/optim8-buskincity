<template>
</template>

<script>
    import { reactive } from "vue";

    export default {
        components: {
        },
        props: {
            page: Object,
            errors: Object,
        },
        setup(props) {
            // state pages
            const page = reactive({
                title: props.page.title,
                slug: props.page.slug,
                data: props.page.data ?? [],
            })

            return {
                page,
            }
        },
    }
</script>
