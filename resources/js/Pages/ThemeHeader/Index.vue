<template>
    <app-layout>
        <template #header>
            Header
        </template>

        <div class="box mb-6">
            <sdb-tab>
                <ul>
                    <sdb-tab-list
                        v-for="(tab, index) in tabs"
                        :key="index"
                        :is-active="isTabActive(index)"
                    >
                        <a @click.prevent="setActiveTab(index)">
                            {{ tab.title }}
                        </a>
                    </sdb-tab-list>
                </ul>
            </sdb-tab>

            <div class="columns">
                <div class="column">
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias quisquam soluta dolores optio temporibus corrupti hic molestiae repudiandae non nostrum labore minima quia, aliquid autem iure numquam mollitia adipisci quas.
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasTab from '@/Mixins/HasTab';
    import SdbTab from '@/Sdb/Tab';
    import SdbTabList from '@/Sdb/TabList';

    export default {
        components: {
            AppLayout,
            SdbTab,
            SdbTabList,
        },
        mixins: [
            MixinHasTab,
        ],
        props: {
            baseRouteName: String,
        },
        setup() {
            return {
                tabs: {
                    layout: { title: 'Layout'},
                    navigation: {title: 'Navigation'},
                },
            }
        },
        data() {
            return {
                activeTab: 'layout',
            };
        },
        methods: {
            onTabSelected(tab) {
                let routeName = this.baseRouteName+'.index';
                if (tab === 'navigation') {
                    routeName = 'admin.theme.header.navigation.index';
                }
                this.$inertia.get(route(routeName));
            },
        }
    }
</script>
