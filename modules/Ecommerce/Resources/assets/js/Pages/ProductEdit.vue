<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';

    export default {
        components: {
            BizErrorNotifications,
        },

        mixins: [
        ],

        layout: AppLayout,

        props: {
        },

        setup(props, { emit }) {
            return {
            };
        },

        data() {
            return {
            };
        },

        methods: {
        },
    };
</script>
