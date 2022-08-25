<template>
    <div>
        <biz-provide-inject-tabs
            v-model="activeTab"
            class="is-boxed"
        >
            <biz-provide-inject-tab title="Builders">
                <form-builder
                    v-model="formBuilder"
                    v-model:content-config-id="computedContentConfigId"
                />
            </biz-provide-inject-tab>
            <biz-provide-inject-tab
                v-if="isEditMode"
                title="Settings"
            >
                Settings Page
            </biz-provide-inject-tab>
        </biz-provide-inject-tabs>
    </div>
</template>

<script>
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs';
    import FormBuilder from './FormBuilder';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Form',

        components: {
            BizProvideInjectTab,
            BizProvideInjectTabs,
            FormBuilder,
        },

        inject: {
            isEditMode: { default: false },
        },

        props: {
            contentConfigId: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },

        emits: [
            'update:contentConfigId',
        ],

        setup(props, {emit}) {
            return {
                formBuilder: useModelWrapper(props, emit),
                computedContentConfigId: useModelWrapper(props, emit, 'contentConfigId'),
            };
        },

        data() {
            return {
                activeTab: 0,
            };
        },
    }
</script>