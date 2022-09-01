<template>
    <form
        class="columns"
        @submit.prevent="$emit('on-submit')"
    >
        <div class="column is-two-thirds">
            <div class="box">
                <h4 class="title is-size-4">
                    Details
                </h4>

                <biz-form-input
                    v-model="form.name"
                    label="Name"
                    :required="true"
                    :message="error('name')"
                />

                <biz-form-input
                    v-model="form.send_to"
                    label="Send To Email"
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
                    label="From Name"
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
                    label="From Email"
                    :message="error('from_email')"
                >
                    <template #afterInput>
                        <notification-tag-option
                            :options="fieldNameOptions"
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
                    label="Reply To"
                    :message="error('reply_to')"
                >
                    <template #afterInput>
                        <notification-tag-option
                            :options="fieldNameOptions"
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

                <biz-form-input-addons
                    v-model="form.bcc"
                    label="Bcc"
                    placeholder="Separate by comma"
                    :message="error('bcc')"
                >
                    <template #afterInput>
                        <notification-tag-option
                            :options="fieldNameOptions"
                            input-name="bcc"
                            @on-select-option="onSelectOption"
                        />
                    </template>

                    <template #note>
                        <p class="help is-info">
                            {{ fieldNotes.bcc }}
                        </p>
                    </template>
                </biz-form-input-addons>

                <biz-form-input-addons
                    v-model="form.subject"
                    label="Subject"
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
                    height="200"
                    label="Message"
                    mode="email"
                >
                    <template #labelAddons>
                        <notification-tag-option
                            :options="fieldNameOptions"
                            input-name="message"
                            @on-select-option="onSelectOption"
                        />
                    </template>
                </biz-form-text-editor>
            </div>
        </div>

        <div class="column">
            <div class="box">
                <h4 class="title is-size-4">
                    Options
                </h4>
                <biz-form-select
                    v-model="form.is_active"
                    label="Is Activated?"
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
                        Cancel
                    </biz-button-link>
                </div>
                <div class="control">
                    <biz-button
                        class="is-link"
                    >
                        {{ !isEditMode ? 'Create' : 'Update' }}
                    </biz-button>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizFormTextEditor from '@/Biz/Form/TextEditor';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import NotificationTagOption from './NotificationTagOption';
    import icon from '@/Libs/icon-class';
    import { useModelWrapper } from '@/Libs/utils';
    import { find, debounce, isEmpty, filter } from 'lodash';
    import { debounceTime } from '@/Libs/defaults';

    export default {
        name: 'Form',

        components: {
            BizFormInput,
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
            return {
                baseRouteName: 'admin.form-builder',
                form: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                icon,
            };
        },

        methods: {
            onSelectOption(optionId, inputName) {
                let appendText = '{' + optionId + '}';

                if (!this.form[inputName]) {
                    this.form[inputName] = appendText;

                    return;
                }

                this.form[inputName] = this.form[inputName] + appendText;
            }
        },
    }
</script>