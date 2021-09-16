export default {
    data() {
        return {
            activeTab: null,
        };
    },
    methods: {
        isTabActive(tab) {
            return this.activeTab === tab;
        },
        setActiveTab(tab) {
            this.activeTab = tab;
            this.onTabSelected(tab);
        },
        onTabSelected() {},
    },
};
