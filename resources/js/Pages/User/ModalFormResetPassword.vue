<template>
    <biz-modal-card
        content-class="is-huge"
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ i18n.user_password_reset }}
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="$emit('close')"
            />
        </template>

        <form @submit.prevent="submit">
            <fieldset>
                <biz-form-input
                    v-model="form.subject"
                    label="Subject"
                    required
                    :message="error('subject', 'default', form.errors)"
                />

                <biz-form-text-editor
                    v-model="form.content"
                    is-required
                    label="Content"
                    :config="messageConfig"
                    :message="error('content', 'default', form.errors)"
                />

                <biz-form-select
                    v-model="form.expiry"
                    label="Expiry"
                    :message="error('expiry', 'default', form.errors)"
                    required
                >
                    <option
                        v-for="expiryOption in expiryOptions"
                        :key="expiryOption.id"
                        :value="expiryOption.id"
                    >
                        {{ expiryOption.value }}
                    </option>
                </biz-form-select>
            </fieldset>
        </form>

        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button @click.prevent="$emit('close')">
                            {{ i18n.cancel }}
                        </biz-button>

                        <biz-button
                            class="is-primary ml-1"
                            @click.prevent="submit"
                        >
                            {{ i18n.send }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormTextEditor from '@/Biz/Form/TextEditor.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import { emailConfig } from '@/Libs/tinymce-configs';
    import { onBeforeMount, ref } from 'vue';
    import { useForm } from '@inertiajs/vue3';
    import { useLoading } from 'vue-loading-overlay';

    export default {
        name: 'ModalFormResetPassword',

        components: {
            BizButton,
            BizFormInput,
            BizFormSelect,
            BizFormTextEditor,
            BizModalCard,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: [
            'i18n',
        ],

        emits: [
            'close',
            'send-email',
        ],

        setup(props, { emit }) {
            const expiryOptions = ref([]);
            const emailTags = ref([]);
            const messageConfig = ref({});
            const form = useForm({
                subject: null,
                content: null,
                expiry: null,
            });
            const $loading = useLoading({});

            onBeforeMount(async () => {
                const url = route('admin.users.password-reset.form-data');

                const loader = $loading.show();

                const response = await axios.get(url);

                expiryOptions.value = response.data?.expiryOptions ?? [];
                emailTags.value = response.data?.emailTags ?? [];
                form.subject = response.data?.defaultSubject ?? null;
                form.content = response.data?.defaultContent ?? null;
                form.expiry = expiryOptions.value[0].id;

                loader.hide();
            });

            messageConfig.value = {...emailConfig, ...{
                height: 350,
                toolbar: 'formatselect | bold italic forecolor backcolor link table' +
                    '| align bullist numlist | removeformat | listTag code',
                setup: (editor) => {
                    editor.ui.registry.addMenuButton('listTag', {
                        text: '{&#183;&#183;&#183;}',
                        fetch: (callback) => {
                            var items = [];

                            emailTags.value.forEach(function (option) {
                                items.push({
                                    type: 'menuitem',
                                    text: _.capitalize(option.replaceAll( '_', ' ')),
                                    onAction: () => editor.insertContent('{'+ option + '}'),
                                })
                            });

                            callback(_.sortBy(items, ['text']));
                        }
                    });
                },
            }};

            const submit = () => {
                emit('send-email', form);
            };

            return {
                emailTags,
                expiryOptions,
                form,
                messageConfig,
                submit,
            };
        },
    };
</script>
