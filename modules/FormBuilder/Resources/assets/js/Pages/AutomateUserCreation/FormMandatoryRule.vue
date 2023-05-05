<template>
    <div>
        <div class="column is-full">
            <biz-form-select
                v-model="selectedEmail"
                required
                :label="i18n.email"
                :message="error('email')"
            >
                <option :value="null">
                    {{ i18n.none }}
                </option>
                <option
                    v-for="formField in emailFieldOptions"
                    :key="formField.id"
                    :value="composeFieldOption(formField)"
                >
                    {{ formField.label }}
                </option>
            </biz-form-select>
        </div>

        <div class="column">
            <biz-form-select
                v-model="selectedFirstName"
                required
                :label="i18n.first_name"
                :message="error('first_name')"
            >
                <option :value="null">
                    {{ i18n.none }}
                </option>
                <option
                    v-for="formField in firstNameFieldOptions"
                    :key="formField.id"
                    :value="composeFieldOption(formField)"
                >
                    {{ formField.label }}
                </option>
            </biz-form-select>
        </div>

        <div class="column">
            <biz-form-select
                v-model="selectedLastName"
                required
                :label="i18n.last_name"
                :message="error('last_name')"
            >
                <option :value="null">
                    {{ i18n.none }}
                </option>
                <option
                    v-for="formField in lastNameFieldOptions"
                    :key="formField.id"
                    :value="composeFieldOption(formField)"
                >
                    {{ formField.label }}
                </option>
            </biz-form-select>
        </div>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors.js';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import { computed } from "vue";
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormMandatoryRule',

        components: {
            BizFormSelect,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: ['i18n'],

        props: {
            modelValue: { type: Object, required: true },
            composeFieldOption: { type: Function, required: true },
            formFields: { type: Array, required: true },
            mandatoryMatchedTypes: { type: Object, required: true },
        },

        setup(props, { emit }) {
            const form = useModelWrapper(props, emit);

            const getSelectedMandatoryField = (name) => {
                if (! form.value[name]) return null;

                const selected = props.formFields.find((field) => field.id == form.value[name].id);

                return props.composeFieldOption(selected);
            };

            const setSelectedMandatoryField = (name, value) => {
                form.value[name] = props.composeFieldOption(value);
            };

            const selectedEmail = computed({
                get: () => getSelectedMandatoryField('email'),
                set: (newValue) => setSelectedMandatoryField('email', newValue),
            });

            const selectedFirstName = computed({
                get: () => getSelectedMandatoryField('first_name'),
                set: (newValue) => setSelectedMandatoryField('first_name', newValue),
            });

            const selectedLastName = computed({
                get: () => getSelectedMandatoryField('last_name'),
                set: (newValue) => setSelectedMandatoryField('last_name', newValue),
            });

            const getPossibleMandatoryFields = (userFieldName) => {
                const possibleTypes = props.mandatoryMatchedTypes[userFieldName];

                return props.formFields.filter((field) => {
                    return possibleTypes.includes(field.type);
                });
            };

            return {
                emailFieldOptions: computed(() => getPossibleMandatoryFields('email')),
                firstNameFieldOptions: computed(() => getPossibleMandatoryFields('first_name')),
                lastNameFieldOptions: computed(() => getPossibleMandatoryFields('last_name')),
                form,
                selectedEmail,
                selectedFirstName,
                selectedLastName,
            };
        },
    };
</script>
