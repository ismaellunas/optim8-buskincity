<template>
    <div>
        <div
            class="tabs"
            :class="classes"
        >
            <ul>
                <li
                    v-for="(tab, index) in content"
                    :key="index"
                    :class="{ 'is-active': selectedIndex === index }"
                >
                    <a @click="selectedIndex = index">
                        <span
                            v-if="tab.icon !== null"
                            class="icon is-small"
                        >
                            <i
                                :class="tab.icon"
                                aria-hidden="true"
                            />
                        </span>
                        <span>
                            {{ tab.name }}
                        </span>
                    </a>
                </li>
            </ul>
        </div>

        <template
            v-for="(tab, index) in content"
            :key="index"
        >
            <div
                v-if="selectedIndex === index"
                class="content"
                v-html="tab.html"
            />
        </template>
    </div>
</template>

<script>
    export default {
        name: 'Tabs',

        props: {
            classes: {type: Object, default:() => {}},
            content: {type: Object, required: true},
        },

        data() {
            return {
                selectedIndex: (
                    this.content.length > 0 ? 0 : null
                ),
            };
        },
    }
</script>