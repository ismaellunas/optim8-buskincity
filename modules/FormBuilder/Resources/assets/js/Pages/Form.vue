<template>
    <div>
        <biz-provide-inject-tabs
            v-model="activeTab"
            class="is-boxed"
        >
            <biz-provide-inject-tab
                title="Builder"
            >
                <form-builder
                    v-model="form"
                    v-model:input-config-id="computedInputConfigId"
                />
            </biz-provide-inject-tab>

            <template v-if="isEditMode">
                <biz-provide-inject-tab title="Notifications">
                    <notification-setting />
                </biz-provide-inject-tab>

                <biz-provide-inject-tab title="Settings">
                    <general-setting />
                </biz-provide-inject-tab>
            </template>
        </biz-provide-inject-tabs>
    </div>
</template>

<script>
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs';
    import FormBuilder from './FormBuilder';
    import NotificationSetting from './Settings/Notification/Index';
    import GeneralSetting from './Settings/General/Index';
    import { useModelWrapper, getPhoneCountries } from '@/Libs/utils';

    export default {
        name: 'Form',

        components: {
            BizProvideInjectTab,
            BizProvideInjectTabs,
            FormBuilder,
            GeneralSetting,
            NotificationSetting,
        },

        inject: {
            isEditMode: { default: false },
        },

        provide() {
            return {
                countryOptions: () => this.countryOptions
            };
        },

        props: {
            inputConfigId: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },

        emits: [
            'update:inputConfigId',
        ],

        setup(props, {emit}) {
            return {
                form: useModelWrapper(props, emit),
                computedInputConfigId: useModelWrapper(props, emit, 'inputConfigId'),
            };
        },

        data() {
            return {
                activeTab: 0,
                countryOptions: [],
            };
        },

        mounted: async function() {
            this.countryOptions = await getPhoneCountries();
        },
    }
</script>