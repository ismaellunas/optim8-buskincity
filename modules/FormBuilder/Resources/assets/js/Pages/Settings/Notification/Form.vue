<template>
    <form
        class="columns"
        @submit.prevent="$emit('on-submit')"
    >
        <div class="column is-two-thirds">
            <div class="box">
                <h4 class="title is-size-4">
                    {{ i18n.details }}
                </h4>

                <biz-form-input
                    v-model="form.name"
                    :label="i18n.name"
                    :required="true"
                    :message="error('name')"
                />

                <biz-form-input
                    v-model="form.send_to"
                    :label="i18n.send_to_email"
                    placeholder="Separate by comma"
                    :required="true"
                    :message="error('send_to')"
                >
                    <template #note>
                        <p class="help is-info">
                            {{ fieldNotes.send_to }}
                        </p>
                    </template>
                </biz-form-input>

                <biz-form-input-addons
                    v-model="form.from_name"
                    :label="i18n.from_name"
                    :message="error('from_name')"
                >
                    <template #afterInput>
                        <notification-tag-option
                            :options="fieldNameOptions"
                            input-name="from_name"
                            @on-select-option="onSelectOption"
                        />
                    </template>

                    <template #note>
                        <p class="help is-info">
                            {{ fieldNotes.from_name }}
                        </p>
                    </template>
                </biz-form-input-addons>

                <biz-form-input-addons
                    v-model="form.from_email"
                    :label="i18n.from_email"
                    :message="error('from_email')"
                >
                    <template #afterInput>
                        <notification-tag-option
                            :options="fieldNameEmailOptions"
                            input-name="from_email"
                            @on-select-option="onSelectOption"
                        />
                    </template>

                    <template #note>
                        <p class="help is-info">
                            {{ fieldNotes.from_email }}
                        </p>
                    </template>
                </biz-form-input-addons>

                <biz-form-input-addons
                    v-model="form.reply_to"
                    :label="i18n.reply_to"
                    :message="error('reply_to')"
                >
                    <template #afterInput>
                        <notification-tag-option
                            :options="fieldNameEmailOptions"
                            input-name="reply_to"
                            @on-select-option="onSelectOption"
                        />
                    </template>

                    <template #note>
                        <p class="help is-info">
                            {{ fieldNotes.reply_to }}
                        </p>
                    </template>
                </biz-form-input-addons>

                <biz-form-input
                    v-model="form.bcc"
                    :label="i18n.bcc"
                    placeholder="Separate by comma"
                    :message="error('bcc')"
                >
                    <template #note>
                        <p class="help is-info">
                            {{ fieldNotes.bcc }}
                        </p>
                    </template>
                </biz-form-input>

                <biz-form-input-addons
                    v-model="form.subject"
                    :label="i18n.subject"
                    :required="true"
                    :message="error('subject')"
                >
                    <template #afterInput>
                        <notification-tag-option
                            :options="fieldNameOptions"
                            input-name="subject"
                            @on-select-option="onSelectOption"
                        />
                    </template>
                </biz-form-input-addons>

                <biz-form-text-editor
                    v-model="form.message"
                    :label="i18n.message"
                    :config="messageConfig"
                />
            </div>
        </div>

        <div class="column">
            <div class="box">
                <h4 class="title is-size-4">
                    {{ i18n.options }}
                </h4>
                <biz-form-select
                    v-model="form.is_active"
                    :label="i18n.is_activated"
                >
                    <option
                        v-for="(option, index) in activeOptions"
                        :key="index"
                        :value="option.id"
                    >
                        {{ option.value }}
                    </option>
                </biz-form-select>
            </div>

            <div class="field is-grouped is-grouped-right">
                <div class="control">
                    <biz-button-link
                        class="is-link is-light"
                        :href="route('admin.form-builders.edit', formBuilderId)"
                    >
                        {{ i18n.cancel }}
                    </biz-button-link>
                </div>
                <div class="control">
                    <biz-button
                        class="is-link"
                    >
                        {{ !isEditMode ? i18n.create : i18n.update }}
                    </biz-button>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormInputAddons from '@/Biz/Form/InputAddons.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormTextEditor from '@/Biz/Form/TextEditor.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import NotificationTagOption from './TagOption.vue';
    import icon from '@/Libs/icon-class';
    import { useModelWrapper } from '@/Libs/utils';
    import { emailConfig } from '@/Libs/tinymce-configs';
    import { filter } from 'lodash';

    export default {
        name: 'Form',

        components: {
            BizFormInput,
            BizFormInputAddons,
            BizFormSelect,
            BizFormTextEditor,
            BizButton,
            BizButtonLink,
            NotificationTagOption,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            formBuilderId: {},
            fieldNotes: { default: () => {} },
            isEditMode: { default: false },
            i18n: { default: () => ({
                details: 'Details',
                send_to_email: 'Send To Email',
                from_name: 'From Name',
                from_email: 'From Email',
                reply_to: 'Reply To',
                bcc: 'Bcc',
                subject: 'Subject',
                message: 'Message',
                options: 'Options',
                is_activated: 'Is Activated?',
                cancel: 'Cancel',
                create: 'Create',
                update: 'Update',
            }) },
        },

        props: {
            activeOptions: { type: Array, default: () => [] },
            fieldNameOptions: { type: Array, default: () => [] },
            modelValue: { type: Object, required: true },
        },

        emits: [
            'on-submit',
        ],

        setup(props, {emit}) {
            const messageConfig = {
                inline: false,
                height: 480,
                plugins: [
                    'table'
                ],
                toolbar: 'formatselect | bold italic forecolor backcolor link table' +
                    '| align bullist numlist | removeformat | listTag',
                setup: (editor) => {
                    editor.ui.registry.addMenuButton('listTag', {
                        text: '{&#183;&#183;&#183;}',
                        fetch: (callback) => {
                            var items = [];

                            props.fieldNameOptions.forEach(function (option) {
                                items.push({
                                    type: 'menuitem',
                                    text: option.value,
                                    onAction: () => editor.insertContent('{'+ option.id + '}'),
                                })
                            });

                            callback(items);
                        }
                    });
                }
            };

            return {
                baseRouteName: 'admin.form-builder',
                form: useModelWrapper(props, emit),
                messageConfig: Object.assign(emailConfig, messageConfig),
            };
        },

        data() {
            return {
                icon,
            };
        },

        computed: {
            fieldNameEmailOptions() {
                return filter(this.fieldNameOptions, {
                    type: 'Email',
                });
            },
        },

        methods: {
            onSelectOption(optionId, inputName) {
                let appendText = '{' + optionId + '}';

                if (!this.form[inputName]) {
                    this.form[inputName] = appendText;

                    return;
                }

                this.form[inputName] = this.form[inputName] + appendText;
            },
        },
    }
</script>