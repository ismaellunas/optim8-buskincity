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
    import { pick } from 'lodash';
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
        },

        mixins: [
            MixinHasLoader,
            MixinHasTab,
        ],

        props: {
            baseRouteName: { type: String, default: '' },
            parentOptions: { type: Object, default: () => {} },
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
                    'parent_id',
                    'is_page_enabled',
                ]),
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
                    onFinish: self.onEndLoadingOverlay
                });
            },
        },
    };
</script>
