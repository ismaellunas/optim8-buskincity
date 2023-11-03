<template>
    <div>
        <biz-form-input
            v-model="computedEntity.label"
            :label="i18n.label"
            :is-small="true"
            @on-blur="populateEntityName()"
        />

        <hr>

        <biz-form-key
            v-model="computedEntity.name"
            :label="i18n.name"
            placeholder="Field name"
            :is-small="true"
            required
        >
            <template #note>
                <p class="help is-info">
                    {{ i18n.name_field_note }}
                </p>
            </template>
        </biz-form-key>
    </div>
</template>

<script>
    import MixinHasTranslation from '@/Mixins/HasTranslation';
    import BizFormKey from '@/Biz/Form/Key.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import { useModelWrapper, convertToKey } from '@/Libs/utils';

    export default {
        name: "ConfigLabelName",

        components: {
            BizFormInput,
            BizFormKey,
        },

        mixins: [
            MixinHasTranslation
        ],

        props: {
            settings: { type: Object, default: () => {} },
            entity: { type: Object, default: () => {} },
        },

        setup(props, { emit }) {
            return {
                computedEntity: useModelWrapper(props, emit, 'entity'),
            };
        },

        methods: {
            populateEntityName() {
                if (
                    ! this.computedEntity.name
                    || this.computedEntity.name == ""
                ) {
                    this.computedEntity.name = convertToKey(this.computedEntity.label);
                }
            },
        },
    }
</script>