<template>
    <div
        v-if="hasChildren"
        class="navbar-item has-dropdown is-hoverable navbar-item-dropdown"
        :class="depth === 0 ? topDropdownClass : ''"
    >
        <a
            class="navbar-link"
            :class="[{ 'is-active': menu.isActive }, { 'is-arrowless': depth > 0 }]"
        >
            {{ menu.title }}
        </a>

        <div class="navbar-dropdown">
            <navbar-dropdown-item
                v-for="(child, index) in menu.children"
                :key="index"
                :menu="child"
                :depth="depth + 1"
            />
        </div>
    </div>
    <a
        v-else
        class="navbar-item"
        :class="[depth === 0 ? topLinkClass : '', { 'is-active': menu.isActive }]"
        :href="menu.link"
        :target="menu.target"
    >
        {{ menu.title }}
    </a>
</template>

<script>
    export default {
        name: 'NavbarDropdownItem',

        props: {
            menu: {
                type: Object,
                required: true,
            },
            depth: {
                type: Number,
                default: 0,
            },
            topDropdownClass: {
                type: String,
                default: '',
            },
            topLinkClass: {
                type: String,
                default: '',
            },
        },

        computed: {
            hasChildren() {
                return Array.isArray(this.menu.children) && this.menu.children.length > 0;
            },
        },
    }
</script>
