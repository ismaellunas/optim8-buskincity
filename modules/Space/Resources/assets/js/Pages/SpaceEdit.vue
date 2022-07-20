<template>
    <app-layout :title="title">
        <template #header>
            {{ title }}
        </template>

        <div class="box mb-6">
            <biz-provide-inject-tabs
                v-model="activeTab"
                class="is-boxed"
            >
                <biz-provide-inject-tab title="Space">
                    <form
                        action=""
                        @submit.prevent="submit"
                    >
                        <space-form
                            v-model="space"
                            :parent-options="parentOptions"
                            :type-options="typeOptions"
                        >
                            <biz-form-select
                                v-model="space.is_page_enabled"
                                label="Is Page Enabled ?"
                            >
                                <option
                                    v-for="(optValue, optKey) in pageEnabledOptions"
                                    :key="optKey"
                                    :value="optValue"
                                    class="is-capitalize"
                                >
                                    {{ optKey }}
                                </option>
                            </biz-form-select>

                            <template #action>
                                <div class="field is-grouped is-grouped-right mt-4">
                                    <div class="control">
                                        <biz-button-link
                                            :href="routeIndex"
                                            class="is-link is-light"
                                        >
                                            Cancel
                                        </biz-button-link>
                                    </div>
                                    <div class="control">
                                        <biz-button class="is-link">
                                            Update
                                        </biz-button>
                                    </div>
                                </div>
                            </template>
                        </space-form>
                    </form>
                </biz-provide-inject-tab>
                <biz-provide-inject-tab title="Manager">
                    <form @submit.prevent="submitManager">
                        <space-manager
                            v-model="managers"
                        >
                            <template #action>
                                <div class="field is-grouped is-grouped-right mt-4">
                                    <div class="control">
                                        <biz-button class="is-link">
                                            Update
                                        </biz-button>
                                    </div>
                                </div>
                            </template>
                        </space-manager>
                    </form>
                </biz-provide-inject-tab>
            </biz-provide-inject-tabs>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasTab from '@/Mixins/HasTab';
    import SpaceForm from './SpaceForm';
    import SpaceManager from './SpaceManager';
    import { pick, map } from 'lodash';
    import { ref } from "vue";
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonLink,
            BizFormSelect,
            BizProvideInjectTab,
            BizProvideInjectTabs,
            SpaceForm,
            SpaceManager,
        },

        mixins: [
            MixinHasLoader,
            MixinHasTab,
        ],

        props: {
            baseRouteName: { type: String, default: '' },
            parentOptions: { type: Object, default: () => {} },
            typeOptions: { type: Object, default: () => {} },
            spaceManagers: { type: Array, default: () => [] },
            spaceRecord: { type: Object, required: true },
            tab: { type: Number, default: 0 },
            title: { type: String, default: "" },
        },

        setup(props) {
            return {
                activeTab: ref(props.tab),
                routeIndex: route(props.baseRouteName+'.index'),
                pageEnabledOptions: { No: false, Yes: true },
            };
        },

        data() {
            return {
                space: pick(this.spaceRecord, [
                    'id',
                    'address',
                    'latitude',
                    'longitude',
                    'name',
                    'type',
                    'parent_id',
                    'is_page_enabled',
                ]),
                managers: this.spaceManagers,
            };
        },

        methods: {
            submit() {
                const self = this;
                const form = useForm(self.space);

                form.put(route(self.baseRouteName+'.update', self.spaceRecord.id), {
                    replace: true,
                    onStart: self.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        self.getSpace();
                        self.onEndLoadingOverlay();
                    }
                });
            },

            submitManager() {
                const self = this;
                const form = useForm({
                    managers: map(this.managers, 'id')
                });

                form.post(route(self.baseRouteName+'.update-managers', self.spaceRecord.id), {
                    replace: true,
                    onStart: self.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onFinish: self.onEndLoadingOverlay
                });
            },

            getSpace() {
                this.space = pick(this.spaceRecord, [
                    'id',
                    'address',
                    'latitude',
                    'longitude',
                    'name',
                    'type',
                    'parent_id',
                    'is_page_enabled',
                ]);
            },
        },
    };
</script>
