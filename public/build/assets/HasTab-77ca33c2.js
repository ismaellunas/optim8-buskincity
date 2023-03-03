const t={data(){return{activeTab:null}},methods:{isTabActive(e){return this.activeTab===e},setActiveTab(e){this.activeTab=e,this.onTabSelected(e)},onTabSelected(){}}};export{t as M};
