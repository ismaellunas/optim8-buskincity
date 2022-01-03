<template>
    <sdb-modal-card
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                Delete User: {{ user.full_name }}
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="$emit('close')"
            />
        </template>

        <div class="content">
            <p>
                What should be done with content owned by the user?
            </p>
        </div>

        <form @submit.prevent="submit">
            <fieldset>
                <div class="field">
                    <div class="control">
                        <sdb-radio
                            v-model="form.is_reassigned"
                            name="delete"
                            :value="false"
                        >
                            Delete all content.
                        </sdb-radio>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <sdb-radio
                            v-model="form.is_reassigned"
                            name="delete"
                            :value="true"
                        >
                            Attribute all content to:
                        </sdb-radio>
                        <sdb-form-select
                            v-model="form.assigned_user"
                            class="is-fullwidth"
                            field-class="ml-4"
                            placeholder="Select a user"
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
                        </sdb-form-select>
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
                        <sdb-button @click="$emit('close')">
                            Cancel
                        </sdb-button>

                        <sdb-button
                            class="is-primary ml-1"
                            type="button"
                            @click="submit"
                        >
                            Delete
                        </sdb-button>
                    </div>
                </div>
            </div>
        </template>
    </sdb-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import SdbModalCard from '@/Sdb/ModalCard';
    import SdbRadio from '@/Sdb/Radio';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        name: 'ModalFormDeleteUser',

        components: {
            SdbButton,
            SdbFormSelect,
            SdbModalCard,
            SdbRadio,
        },

        mixins: [
            MixinHasPageErrors,
        ],

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
