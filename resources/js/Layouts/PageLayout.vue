<template>
    <nav class="navbar is-dark">
        <div class="navbar-brand">
            <sdb-link class="navbar-item" href="/">
                <img src="https://dummyimage.com/48x28/e5e5e5/000000.png&text=B+752" alt="" height="28">
            </sdb-link>
            <div class="navbar-burger burger" data-target="navbarExampleTransparentExample">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <div id="navbarExampleTransparentExample" class="navbar-menu">
            <div class="navbar-start">
                <sdb-link href="/" class="navbar-item">Home</sdb-link>
                <sdb-link
                    v-for="menuItem in menus.navbar"
                    :href="menuItem.link"
                    class="navbar-item">
                    {{ menuItem.title }}
                </sdb-link>
            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="field">
                        <div class="control">
                            <input class="input" type="text" placeholder="Search . . .">
                        </div>
                    </div>
                </div>
                <div class="navbar-item has-dropdown is-hoverable">
                    <span class="navbar-link">{{ currentLanguage.toUpperCase() }}</span>
                    <div class="navbar-dropdown is-boxed">
                        <sdb-link
                            v-for="language in availableLanguages"
                            :href="route('language.change', [language.id])" class="navbar-item">
                            {{ language.id.toUpperCase() }}
                        </sdb-link>
                    </div>
                </div>
                <sdb-link :href="route('login')" class="navbar-item pr-5">Login</sdb-link>
            </div>
        </div>
    </nav>

    <slot></slot>

</template>

<script>
    import SdbLink from '@/Sdb/Link';

    export default {
        props: [
            'currentLanguage',
            'languageOptions',
            'menus',
            'user',
            //'errorBags',
            //'flash',
            //'jetstream',
            //'socialstream',
        ],
        components: { SdbLink },
        computed: {
            availableLanguages() {
                return this
                    .languageOptions
                    .filter(option => option.id !== this.currentLanguage);
            },
        }
    }
</script>
