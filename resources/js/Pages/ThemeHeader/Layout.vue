<template>
    <section>
        <header-layout
            ref="headerLayout"
            :setting="settings.header_layout"
        />

        <hr>
        <header-logo
            :setting="settings.header_logo_url"
        />
    </section>
</template>

<script>
    import HeaderLayout from './HeaderLayout';
    import HeaderLogo from './HeaderLogo';

    export default {
        name: 'Layout',

        components: {
            HeaderLayout,
            HeaderLogo,
        },

        props: {
            settings: {
                type: Object,
                required: true,
            },
        },
    }
</script>