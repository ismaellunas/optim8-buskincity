<template>
    <nav class="navbar level is-primary mb-0">
        <div class="level-item">
            <div
                class="navbar-menu navbarExampleTransparentExample"
            >
                <div class="navbar-end">
                    <template
                        v-for="(menu, index) in menus"
                        :key="index"
                    >
                        <template v-if="menu.children.length > 0">
                            <div
                                v-if="index <= middleIndexMenu"
                                class="navbar-item has-dropdown is-hoverable"
                            >
                                <a
                                    class="navbar-link"
                                >
                                    {{ menu.title }}
                                </a>
                                <div class="navbar-dropdown">
                                    <template
                                        v-for="childMenu in menu.children"
                                        :key="childMenu.id"
                                    >
                                        <biz-link
                                            class="navbar-item"
                                            :href="childMenu.link"
                                        >
                                            {{ childMenu.title }}
                                        </biz-link>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <biz-link
                                v-if="index <= middleIndexMenu"
                                class="navbar-item"
                                :href="menu.link"
                            >
                                {{ menu.title }}
                            </biz-link>
                        </template>
                    </template>
                </div>
            </div>
        </div>
        <div class="level-item">
            <div class="navbar-brand">
                <biz-link
                    class="navbar-item"
                    :href="route('homepage')"
                >
                    <img
                        :src="logoUrl"
                        alt=""
                        height="28"
                    >
                </biz-link>

                <div
                    class="navbar-burger burger"
                    data-target="navbarExampleTransparentExample"
                >
                    <span />
                    <span />
                    <span />
                </div>
            </div>
        </div>
        <div class="level-item">
            <div
                class="navbar-menu navbarExampleTransparentExample"
            >
                <div class="navbar-start">
                    <template
                        v-for="(menu, index) in menus"
                        :key="index"
                    >
                        <template v-if="menu.children.length > 0">
                            <div
                                v-if="index > middleIndexMenu"
                                class="navbar-item has-dropdown is-hoverable"
                            >
                                <a
                                    class="navbar-link"
                                >
                                    {{ menu.title }}
                                </a>
                                <div class="navbar-dropdown">
                                    <template
                                        v-for="childMenu in menu.children"
                                        :key="childMenu.id"
                                    >
                                        <biz-link
                                            class="navbar-item"
                                            :href="childMenu.link"
                                        >
                                            {{ childMenu.title }}
                                        </biz-link>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <biz-link
                                v-if="index > middleIndexMenu"
                                class="navbar-item"
                                :href="menu.link"
                            >
                                {{ menu.title }}
                            </biz-link>
                        </template>
                    </template>

                    <div class="navbar-item has-dropdown is-hoverable">
                        <span class="navbar-link">{{ currentLanguage.toUpperCase() }}</span>
                        <div class="navbar-dropdown is-boxed">
                            <a
                                v-for="language in availableLanguages"
                                :key="language.id"
                                class="navbar-item"
                                :href="route('language.change', [language.id])"
                            >
                                {{ language.id.toUpperCase() }}
                            </a>
                        </div>
                    </div>
                    <biz-link
                        :href="route('login')"
                        class="navbar-item pr-5"
                    >
                        Login
                    </biz-link>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
    import BizLink from '@/Biz/Link';

    export default {
        name: 'NavbarLayoutTwo',

        components: {
            BizLink,
        },

        props: {
            availableLanguages: {type: Array, default: []},
            currentLanguage: {type: String, default: "en"},
            logoUrl: {type: String, required: true},
            menus: {type: Array, default: []},
        },

        computed: {
            middleIndexMenu() {
                let totalMenu = this.menus.length;
                return Math.floor(totalMenu / 2);
            },
        },
    }
</script>