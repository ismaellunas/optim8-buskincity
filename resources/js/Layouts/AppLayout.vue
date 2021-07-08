<template>
    <div id="main-container-wrapper">

        <sdb-navbar-menu></sdb-navbar-menu>

        <!-- <jet-banner /> -->

        <section class="hero is-small is-primary">
            <div class="hero-body">
                <div class="container">
                    <p class="title">
                        <slot name="header"></slot>
                    </p>
                </div>
            </div>
        </section>

        <div id="main-container" class="container mt-4">
            <!-- Page Content -->
            <main>
                <slot></slot>
            </main>
        </div>
    </div>
</template>

<script>
    import JetApplicationMark from '@/Jetstream/ApplicationMark'
    import JetBanner from '@/Jetstream/Banner'
    import JetDropdown from '@/Jetstream/Dropdown'
    import JetDropdownLink from '@/Jetstream/DropdownLink'
    import JetNavLink from '@/Jetstream/NavLink'
    import JetResponsiveNavLink from '@/Jetstream/ResponsiveNavLink'
    import SdbNavbarMenu from '@/Sdb/NavbarMenu';

    export default {
        components: {
            JetApplicationMark,
            JetBanner,
            JetDropdown,
            JetDropdownLink,
            JetNavLink,
            JetResponsiveNavLink,
            SdbNavbarMenu,
        },

        data() {
            return {
                showingNavigationDropdown: false,
            }
        },

        methods: {
            switchToTeam(team) {
                this.$inertia.put(route('current-team.update'), {
                    'team_id': team.id
                }, {
                    preserveState: false
                })
            },

            logout() {
                this.$inertia.post(route('logout'));
            },
        }
    }
</script>
