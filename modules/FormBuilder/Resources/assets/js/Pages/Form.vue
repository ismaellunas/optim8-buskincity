<template>
    <div>
        <biz-provide-inject-tabs
            v-model="activeTab"
            class="is-boxed"
        >
            <biz-provide-inject-tab
                :title="i18n.builder"
            >
                <form-builder
                    v-model="form"
                    v-model:input-config-id="computedInputConfigId"
                />
            </biz-provide-inject-tab>

            <template v-if="isEditMode">
                <biz-provide-inject-tab :title="i18n.notifications">
                    <notification-setting />
                </biz-provide-inject-tab>

                <biz-provide-inject-tab :title="i18n.settings">
                    <general-setting />
                </biz-provide-inject-tab>

                <biz-provide-inject-tab :title="i18n.automate_user_creation">
                    <automate-user-creation-setting />
                </biz-provide-inject-tab>
            </template>
        </biz-provide-inject-tabs>
    </div>
</template>

<script>
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab.vue';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs.vue';
    import FormBuilder from './FormBuilder.vue';
    import NotificationSetting from './Settings/Notification/Index.vue';
    import AutomateUserCreationSetting from './AutomateUserCreation/Form.vue';
    import GeneralSetting from './Settings/General/Index.vue';
    import { useModelWrapper, getPhoneCountries } from '@/Libs/utils';

    export default {
        name: 'FormBuilderForm',

        components: {
            BizProvideInjectTab,
            BizProvideInjectTabs,
            FormBuilder,
            GeneralSetting,
            NotificationSetting,
            AutomateUserCreationSetting,
        },

        inject: {
            isEditMode: { default: false },
            i18n: { default: () => ({
                builder: 'Builder',
                notifications : 'Notifications',
                settings : 'Settings',
            }) },
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
