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
                    v-model="formBuilder"
                    v-model:input-config-id="computedInputConfigId"
                />
            </biz-provide-inject-tab>
            <biz-provide-inject-tab
                v-if="isEditMode"
                title="Settings"
            >
                <setting />
            </biz-provide-inject-tab>
        </biz-provide-inject-tabs>
    </div>
</template>

<script>
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs';
    import FormBuilder from './FormBuilder';
    import Setting from './Setting';
    import { useModelWrapper, getPhoneCountries } from '@/Libs/utils';

    export default {
        name: 'Form',

        components: {
            BizProvideInjectTab,
            BizProvideInjectTabs,
            FormBuilder,
            Setting,
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
                formBuilder: useModelWrapper(props, emit),
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