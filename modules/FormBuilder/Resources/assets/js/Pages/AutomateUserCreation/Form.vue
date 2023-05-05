<template>
    <div class="pb-6">
        <form
            v-if="form"
            @submit.prevent="save"
        >
            <div class="px-3">
                <div class="columns">
                    <div class="column is-8">
                        <h4 class="title is-size-4">
                            {{ i18n.mandatory_fields }}
                        </h4>

                        <form-mandatory-rule
                            v-model="form"
                            class="columns is-multiline"
                            :form-fields="formFields"
                            :compose-field-option="composeFieldOption"
                            :mandatory-matched-types="mandatoryMatchedTypes"
                        />
                    </div>

                    <div class="column is-4">
                        <h4 class="title is-size-4">
                            {{ i18n.role_that_will_be_assigned }}
                        </h4>

                        <div class="columns">
                            <div class="column">
                                <biz-form-select
                                    v-model="form.role"
                                    required
                                    :label="i18n.role"
                                    :message="error('role')"
                                    @change="onRoleChanged"
                                >
                                    <option :value="null">
                                        {{ i18n.none }}
                                    </option>
                                    <option
                                        v-for="roleOption in roleOptions"
                                        :key="roleOption.id"
                                        :value="roleOption.id"
                                    >
                                        {{ roleOption.value }}
                                    </option>
                                </biz-form-select>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>

                <h4 class="title is-size-4">
                    {{ i18n.mapping_rules }}
                </h4>

                <div class="columns">
                    <div class="column has-text-right">
                        <biz-button-icon
                            class="is-primary"
                            type="button"
                            :disabled="!canOpenAddMappingRule"
                            :icon="iconAdd"
                            @click.prevent="openModal"
                        >
                            <span>{{ i18n.add }}</span>
                        </biz-button-icon>
                    </div>
                </div>

                <biz-table
                    class="is-stripped is-hoverable mb-4"
                    is-fullwidth
                >
                    <thead>
                        <tr>
                            <th>{{ i18n.form_field }}</th>
                            <th>{{ i18n.user_field }}</th>
                            <th>{{ i18n.actions }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <row-mapping-rule
                            v-for="(fieldRecord, index) in form.mapping_rules"
                            :key="index"
                            :form-fields="formFields"
                            :user-fields="userFields"
                            :rule="fieldRecord"
                            @delete-rule="deleteMappingRule"
                        />
                    </tbody>
                </biz-table>
                <hr>

                <h4 class="title is-size-4">
                    {{ i18n.email_templates }}
                </h4>

                <div class="columns is-multiline">
                    <div class="column is-full">
                        <biz-form-text-editor
                            v-model="form.create_user_email"
                            :config="messageConfig"
                            :label="i18n.user_creation"
                        />
                    </div>

                    <div class="column is-full">
                        <biz-form-text-editor
                            v-model="form.update_user_email"
                            :config="messageConfig"
                            :label="i18n.user_update"
                        />
                    </div>
                </div>
            </div>

            <div class="columns mt-4">
                <div class="column">
                    <biz-button class="is-link is-pulled-right">
                        {{ i18n.update }}
                    </biz-button>
                </div>
            </div>
        </form>

        <modal-add-mapping-rule
            v-if="isModalOpen"
            :form-fields="formFields"
            :user-fields="userFieldOptions"
            :matched-types="matchedTypes"
            :mapping-rules="form.mapping_rules"
            @add-mapped-field="addMappingRule"
            @close="closeModal"
        />
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormTextEditor from '@/Biz/Form/TextEditor.vue';
    import BizTable from '@/Biz/Table.vue';
    import MixinHasLoader from '@/Mixins/HasLoader.js';
    import MixinHasModal from '@/Mixins/HasModal.js';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors.js';
    import ModalAddMappingRule from './ModalAddMappingRule.vue';
    import RowMappingRule from './RowMappingRule.vue';
    import FormMandatoryRule from './FormMandatoryRule.vue';
    import { add as iconAdd } from '@/Libs/icon-class';
    import { computed, ref } from "vue";
    import { confirm as confirmAlert, oops as oopsAlert, success as successAlert, confirmDelete } from '@/Libs/alert';
    import { emailConfig } from '@/Libs/tinymce-configs';
    import { router, useForm, usePage } from '@inertiajs/vue3';

    export default {
        name: 'AutomateUserCreationForm',

        components: {
            BizButton,
            BizButtonIcon,
            BizFormSelect,
            BizFormTextEditor,
            BizTable,
            ModalAddMappingRule,
            RowMappingRule,
            FormMandatoryRule,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
            MixinHasPageErrors,
        ],

        provide() {
            return {
                i18n: this.i18n,
            };
        },

        emits: ['update:modelValue'],

        setup() {
            const emailTags = computed(() => usePage().props.emailTags);
            const formBuilder = computed(() => usePage().props.formBuilder);
            const mappingRules = computed(() => usePage().props.mappingRules);

            const messageConfig = {...emailConfig, ...{
                height: 350,
                toolbar: 'formatselect | bold italic forecolor backcolor link table' +
                    '| align bullist numlist | removeformat | listTag',
                setup: (editor) => {
                    editor.ui.registry.addMenuButton('listTag', {
                        text: '{&#183;&#183;&#183;}',
                        fetch: (callback) => {
                            var items = [];

                            emailTags.value.forEach(function (option) {
                                items.push({
                                    type: 'menuitem',
                                    text: _.capitalize(_.replace(option, '_', ' ')),
                                    onAction: () => editor.insertContent('{'+ option + '}'),
                                })
                            });

                            callback(_.sortBy(items, ['text']));
                        }
                    });
                },
            }};

            const form = useForm({
                email: mappingRules.value.mandatoryFields.email ?? null,
                first_name: mappingRules.value.mandatoryFields.first_name ?? null,
                last_name: mappingRules.value.mandatoryFields.last_name ?? null,
                mapping_rules: _.clone(mappingRules.value.optionalFields) ?? [],
                role: mappingRules.value.role ?? null,
                create_user_email: formBuilder.value.setting?.email?.automate_user_creation ?? null,
                update_user_email: formBuilder.value.setting?.email?.automate_user_update ?? null,
            });

            return {
                formFields: computed(() => usePage().props.fields),
                i18n: computed(() => usePage().props.i18n),
                mandatoryMatchedTypes: computed(() => usePage().props.mandatoryMatchedTypes),
                matchedTypes: computed(() => usePage().props.matchedTypes),
                roleOptions: computed(() => usePage().props.roleOptions),
                userFields: computed(() => usePage().props.userFields),
                form,
                formBuilder,
                mappingRules,
                iconAdd,
                prevRole: ref(mappingRules.value.role ?? null),
                messageConfig,
                emailTags,
            };
        },

        computed: {
            userFieldOptions() {
                const role = this.form.role;

                return this.userFields
                    .filter((field) => {
                        let isValid = ! field.not_in_roles.includes(role);

                        if (isValid && field.roles.length > 0) {
                            isValid = field.roles.includes(role);
                        }

                        return isValid;
                    });
            },

            canOpenAddMappingRule() {
                const unmappedUserFields = _.filter(this.userFieldOptions, (field) => {
                    return (! _.find(
                        this.form.mapping_rules,
                        ['to.name', field.name])
                    );
                });

                return (
                    this.formFields.length
                    && this.userFields.length
                    && ! _.isEmpty(unmappedUserFields)
                );
            },

        },

        methods: {
            addMappingRule(mappingRule) {
                mappingRule.to = this.composeFieldOption(mappingRule.to);
                mappingRule.from = this.composeFieldOption(mappingRule.from);

                this.form.mapping_rules.push(_.clone(mappingRule));
                this.closeModal();
            },

            deleteMappingRule(id) {
                this.form.mapping_rules = this.form.mapping_rules.filter((rule) => {
                    return rule.id != id;
                });
            },

            composeFieldOption(field) {
                return _.pick(field, ['form_id', 'id', 'name']);
            },

            save() {
                this.form.post(
                    route('admin.form-builders.automate-user-creation.mapped-fields.save', this.formBuilder.id),
                    {
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => this.onEndLoadingOverlay(),
                        onSuccess: (page) => successAlert(page.props.flash?.message ?? ''),
                    }
                );
            },

            onRoleChanged(event) {
                if (_.isEmpty(this.form.mapping_rules)) {

                    this.form.role = parseInt(event.target.value);
                    this.prevRole = parseInt(event.target.value);

                } else {

                    confirmAlert(
                        this.i18n.change_role_confirmation_title,
                        this.i18n.change_role_confirmation_text,
                        this.i18n.continue,
                        { icon: 'warning' }
                    )
                        .then((result) => {
                            if (result.isConfirmed) {
                                this.form.role = parseInt(event.target.value);
                                this.prevRole = parseInt(event.target.value);

                                this.removeUnusedMappingRules();
                            } else {
                                this.form.role = this.prevRole;
                            }
                        });
                }
            },

            removeUnusedMappingRules() {
                this.form.mapping_rules = [];
            },
        },
    };
</script>
