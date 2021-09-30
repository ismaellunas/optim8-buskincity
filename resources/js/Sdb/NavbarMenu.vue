<template>
    <nav class="navbar has-shadow" role="navigation" aria-label="main navigation">
        <div class="container">
            <div class="navbar-brand">
                <sdb-link :href="route('dashboard')" class="navbar-item">
                    <jet-application-mark class="" />
                </sdb-link>

                <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </a>
            </div>

            <div id="navbarBasicExample" class="navbar-menu">
                <div class="navbar-start">
                    <jet-nav-link :href="route('dashboard')" :active="route().current('dashboard')">
                        Dashboard
                    </jet-nav-link>
                    <jet-nav-link :href="route('admin.pages.index')" :active="route().current('dashboard')">
                        Pages
                    </jet-nav-link>
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link">
                            Blog
                        </a>
                        <div class="navbar-dropdown">
                            <sdb-link :href="route('admin.posts.index')" class="navbar-item">
                                Posts
                            </sdb-link>
                            <sdb-link :href="route('admin.categories.index')" class="navbar-item">
                                Categories
                            </sdb-link>
                        </div>
                    </div>
                    <jet-nav-link
                        :href="route('admin.media.index')"
                        :active="route().current('admin.media.*')">
                        Media
                    </jet-nav-link>
                    <jet-nav-link
                        :href="route('admin.users.index')"
                        :active="route().current('admin.users.*')">
                        Users
                    </jet-nav-link>
                    <jet-nav-link
                        class="navbar-item"
                        :href="route('admin.roles.index')"
                        :active="route().current('admin.roles.*')">
                        Roles
                    </jet-nav-link>
                    <!--
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link">
                            UAC
                        </a>
                        <div class="navbar-dropdown">
                            <jet-nav-link
                                class="navbar-item"
                                :href="route('admin.permissions.index')"
                                :active="route().current('admin.permissions.*')">
                                Permissions
                            </jet-nav-link>
                            <jet-nav-link
                                class="navbar-item"
                                :href="route('admin.user-roles.index')"
                                :active="route().current('admin.user-roles.index')">
                                UserRole
                            </jet-nav-link>
                        </div>
                    </div>
                    -->
                </div>

                <div class="navbar-end">
                    <div class="navbar-item">
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link">
                                {{ $page.props.user.name }}
                            </a>

                            <div class="navbar-dropdown is-boxed">
                                <jet-dropdown-link :href="route('profile.show')" class="navbar-item">
                                    Profile
                                </jet-dropdown-link>

                                <jet-dropdown-link :href="route('api-tokens.index')" v-if="$page.props.jetstream.hasApiFeatures" class="navbar-item">
                                    API Tokens
                                </jet-dropdown-link>

                                <hr class="navbar-divider">

                                <form method="POST" @submit.prevent="logout">
                                    <jet-responsive-nav-link as="button" class="navbar-item">
                                        Log Out
                                    </jet-responsive-nav-link>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
    import JetApplicationMark from '@/Jetstream/ApplicationMark';
    import JetDropdown from '@/Jetstream/Dropdown';
    import JetDropdownLink from '@/Jetstream/DropdownLink';
    import JetNavLink from '@/Jetstream/NavLink';
    import JetResponsiveNavLink from '@/Jetstream/ResponsiveNavLink';
    import SdbLink from '@/Sdb/Link';

    export default {
        components: {
            JetApplicationMark,
            JetDropdown,
            JetDropdownLink,
            JetNavLink,
            JetResponsiveNavLink,
            SdbLink,
        },
        methods: {
            switchToTeam(team) {
                this.$inertia.put(route('current-team.update'), {
                    'team_id': team.id
                }, {
                    preserveState: false
                });
            },
            logout() {
                this.$inertia.post(route('logout'));
            },
        }
    }
</script>
