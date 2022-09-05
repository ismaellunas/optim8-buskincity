<template>
    <div>
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />
        <biz-form-phone
            v-model="value"
            :label="entity.label"
            :required="entity.validation.rules.required"
            :disabled="entity.disabled"
            :readonly="entity.readonly"
            :placeholder="entity.placeholder"
        >
            <template
                v-if="entity.note"
                #note
            >
                <p
                    class="help"
                >
                    {{ entity.note }}
                </p>
            </template>
        </biz-form-phone>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import BizFormPhone from '@/Biz/Form/Phone';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Phone',

        components: {
            BizToolbarContent,
            BizFormPhone,
        },

        mixins: [
            MixinDeletableContent,
            MixinDuplicableContent,
        ],

        props: {
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },

        setup(props, { emit }) {
            return {
                entity: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                value: {
                    country: 'US',
                    number: null,
                },
            };
        },
    };
</script>