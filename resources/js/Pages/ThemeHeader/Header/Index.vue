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

            <header-layout
                :last-saved="headerLayoutLastSaved"
                :setting="settings['header_layout']"
            ></header-layout>

            <hr>
            <header-logo
                :last-saved="headerLogoUrlLastSaved"
                :setting="settings['header_logo_url']"
            ></header-logo>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasTab from '@/Mixins/HasTab';
    import SdbButton from '@/Sdb/Button';
    import SdbTab from '@/Sdb/Tab';
    import SdbTabList from '@/Sdb/TabList';
    import HeaderLayout from './Layout';
    import HeaderLogo from './Logo';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbTab,
            SdbTabList,
            HeaderLayout,
            HeaderLogo,
        },
        mixins: [
            MixinHasTab,
        ],
        props: {
            baseRouteName: String,
            headerLayoutLastSaved: {type: String, default: '-'},
            headerLogoUrlLastSaved: {type: String, default: '-'},
            settings: {type: Object, required: true},
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
