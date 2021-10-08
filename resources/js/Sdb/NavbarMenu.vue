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
                    <template
                        v-for="(menu, index) in navMenus"
                        :key="index"
                    >
                        <template v-if="menu.children && menu.isEnabled">
                            <div class="navbar-item has-dropdown is-hoverable">
                                <a
                                    class="navbar-link"
                                    :class="{'is-active': menu.isActive}"
                                >
                                    {{ menu.title }}
                                </a>
                                <div class="navbar-dropdown">
                                    <template
                                        v-for="childMenu in menu.children"
                                    >
                                        <sdb-link
                                            v-if="childMenu.isEnabled"
                                            class="navbar-item"
                                            :href="childMenu.link"
                                            :class="{'is-active': menu.isActive}"
                                        >
                                            {{ childMenu.title }}
                                        </sdb-link>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <jet-nav-link
                                v-if="menu.isEnabled"
                                :active="menu.isActive"
                                :href="menu.link"
                            >
                                {{ menu.title }}
                            </jet-nav-link>
                        </template>
                    </template>
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
    import { computed } from 'vue';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            JetApplicationMark,
            JetDropdown,
            JetDropdownLink,
            JetNavLink,
            JetResponsiveNavLink,
            SdbLink,
        },
        setup() {
            const navMenus = computed(() => usePage().props.value.menus.nav);
            return {
                navMenus,
            };
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
