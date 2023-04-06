<template>
    <biz-modal-card
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ i18n.delete_user }}: {{ user.full_name }}
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="$emit('close')"
            />
        </template>

        <div class="content">
            <p>
                {{ i18n.delete_user_action }}
            </p>
        </div>

        <form @submit.prevent="submit">
            <fieldset>
                <div class="field">
                    <div class="control">
                        <biz-radio
                            v-model="form.is_reassigned"
                            name="delete"
                            :value="false"
                        >
                            {{ i18n.delete_all_content }}
                        </biz-radio>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <biz-radio
                            v-model="form.is_reassigned"
                            name="delete"
                            :value="true"
                        >
                            {{ i18n.attribute_all_content_to }}:
                        </biz-radio>
                        <biz-form-select
                            v-model="form.assigned_user"
                            class="is-fullwidth"
                            field-class="ml-4"
                            :placeholder="i18n.select_user"
                            :disabled="!form.is_reassigned"
                            :message="error('assigned_user', 'deleteUser', form.errors)"
                        >
                            <option
                                v-for="option in candidates"
                                :key="option.id"
                                :value="option.id"
                            >
                                {{ option.full_name }}
                            </option>
                        </biz-form-select>
                    </div>
                </div>
            </fieldset>
        </form>

        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button @click="$emit('close')">
                            {{ i18n.cancel }}
                        </biz-button>

                        <biz-button
                            class="is-primary ml-1"
                            type="button"
                            @click="submit"
                        >
                            {{ i18n.delete }}
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
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizRadio from '@/Biz/Radio.vue';
    import { useForm } from '@inertiajs/vue3';

    export default {
        name: 'ModalFormDeleteUser',

        components: {
            BizButton,
            BizFormSelect,
            BizModalCard,
            BizRadio,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            i18n: { default: () => ({
                delete_user : 'Delete User',
                delete_user_action : 'What should be done with content owned by the user?',
                delete_all_content : 'Delete all content',
                attribute_all_content_to : 'Attribute all content to',
                select_user : 'Select a user',
                cancel : 'Cancel',
                delete : 'Delete',
            }) },
        },

        props: {
            errors: {
                type: Object,
                default: () => {},
            },
            user: {
                type: Object,
                required: true,
            },
            getCandidatesRoute: {
                type: String,
                required: true,
            }
        },

        emits: [
            'close',
            'delete-user',
        ],

        setup(props) {
            return {
                form: useForm({
                    assigned_user: null,
                    is_reassigned: false,
                }),
            };
        },

        data() {
            return {
                candidates: [],
                loader: null,
            };
        },

        mounted() {
            this.loadCandidates();
        },

        methods: {
            submit() {
                this.$emit('delete-user', this.form);
            },

            loadCandidates() {
                const self = this;

                self.loader = self.$loading.show();

                const url = route(
                    self.getCandidatesRoute,
                    self.user.id
                );

                return axios
                    .get(url)
                    .then((response) => {
                        self.candidates = response.data;
                    })
                    .then(() => {
                        self.loader.hide();
                    });
            },
        },
    };
</script>
