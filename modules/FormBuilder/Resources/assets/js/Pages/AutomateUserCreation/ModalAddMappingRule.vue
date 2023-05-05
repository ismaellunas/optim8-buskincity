<template>
    <biz-modal-card
        :is-close-hidden="true"
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title">
                {{ i18n.map_form_field_to_user_field }}
            </p>
            <biz-button
                aria-label="close"
                class="delete is-primary"
                type="button"
                @click="$emit('close')"
            />
        </template>

        <form @submit.prevent="">
            <biz-form-select
                v-model="mappingRule.from"
                required
                :label="i18n.field"
                @change="resetUserField"
            >
                <option
                    v-for="formField in formFields"
                    :key="formField.id"
                    :value="formField"
                >
                    {{ formField.label }}
                </option>
            </biz-form-select>

            <biz-form-select
                v-model="mappingRule.to"
                required
                :disabled="userFieldOptions.length <= 0"
                :label="i18n.user_field"
            >
                <option
                    v-for="formField in userFieldOptions"
                    :key="formField.id"
                    :value="formField"
                >
                    {{ formField.label }}
                </option>
            </biz-form-select>
        </form>

        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div class="column has-text-right">
                    <biz-button-icon
                        class="is-primary"
                        type="button"
                        :disabled="!canAddMappingRule"
                        :icon="iconAdd"
                        @click="addMappingRule"
                    >
                        <span>{{ i18n.add }}</span>
                    </biz-button-icon>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import { add as iconAdd } from '@/Libs/icon-class';
    import { ref } from "vue";

    export default {
        name: 'ModalAddMappingRule',

        components: {
            BizButton,
            BizButtonIcon,
            BizFormSelect,
            BizModalCard,
        },

        inject: ['i18n'],

        props: {
            formFields: { type: Array, required: true },
            mappingRules: { type: Array, default: () => [] },
            matchedTypes: { type: Object, required: true },
            userFields: { type: Array, required: true },
        },

        emits: [
            'add-mapped-field',
            'close',
        ],

        setup(props) {
            return {
                mappingRule: ref({
                    id: _.uniqueId('_'),
                    from: null,
                    to: null
                }),
                iconAdd,
            };
        },

        computed: {
            canAddMappingRule() {
                return this.mappingRule.from && this.mappingRule.to;
            },

            userFieldOptions() {
                const type = _.get(this.mappingRule, 'from.type');

                if (type) {
                    const possibleTypes = this.matchedTypes[type];

                    return this.userFields.filter((field) => {
                        return (
                            possibleTypes.includes(field.type)
                            && ! _.find(this.mappingRules, ['to.name', field.name])
                        );
                    });
                }

                return [];
            },
        },

        methods: {
            addMappingRule() {
                this.$emit('add-mapped-field', this.mappingRule);
            },

            resetUserField() {
                this.mappingRule.to = null;
            },
        },
    };
</script>
