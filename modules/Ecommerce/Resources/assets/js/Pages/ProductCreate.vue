<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="mb-6">
            <form
                method="post"
                @submit.prevent="submit"
            >
                <div class="box">
                    <h5 class="title is-5">
                        Details
                    </h5>

                    <biz-form-input
                        v-model="form.name"
                        label="Title"
                        :message="error('name')"
                        required
                    />

                    <biz-form-input
                        v-model="form.description"
                        label="Description"
                        :message="error('description')"
                        required
                    />

                    <biz-form-select
                        v-model="form.status"
                        label="Status"
                        required
                        :message="error('status')"
                    >
                        <option
                            v-for="statusOption in statusOptions"
                            :key="statusOption.id"
                            :value="statusOption.id"
                        >
                            {{ statusOption.value }}
                        </option>
                    </biz-form-select>

                    <h5 class="title is-5">
                        Visibility
                    </h5>

                    <biz-form-select
                        v-model="form.roles"
                        label="Roles"
                        required
                        :message="error('roles')"
                    >
                        <option
                            v-for="roleOption in roleOptions"
                            :key="roleOption.id"
                            :value="roleOption.id"
                        >
                            {{ roleOption.value }}
                        </option>
                    </biz-form-select>

                    <h5 class="title is-5">
                        Images
                    </h5>

                    <div class="field is-grouped is-grouped-right">
                        <div class="control">
                            <biz-button-link
                                :href="route(baseRouteName+'.index')"
                                class="is-link is-light"
                            >
                                Cancel
                            </biz-button-link>
                        </div>
                        <div class="control">
                            <biz-button class="is-link">
                                Create
                            </biz-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            BizFormInput,
            BizFormSelect,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: {type: String, required: true},
            roleOptions: { type: Array, required: true },
            statusOptions: { type: Array, required: true },
        },

        setup(props, { emit }) {
            const form = {
                name: null,
                status: 'draft',
                description: null,
                roles: [],
                images: [],
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
            };
        },

        methods: {
            submit() {
                const self = this;
                const url = route(this.baseRouteName + '.store');

                this.form.post(url, {
                    onStart: self.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: self.onEndLoadingOverlay,
                });
            },
        },
    };
</script>
