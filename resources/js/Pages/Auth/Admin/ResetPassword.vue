<template>
    <layout-admin title="Reset Password">
        <template #back>
            <biz-link :href="route('admin.login')">
                <biz-icon :icon="iconBack" />
                <span>Back</span>
            </biz-link>
        </template>

        <template #default>
            <div class="mb-4">
                <h1 class="title">
                    Reset Password
                </h1>
                <h2 class="subtitle">
                    <span>Lorem ipsum dolor sit amet.</span>
                </h2>
            </div>

            <biz-error-notifications
                :errors="$page.props.errors"
            />

            <form @submit.prevent="submit">
                <div>
                    <biz-form-input
                        v-model="form.email"
                        label="Email"
                        required
                        type="email"
                        disabled
                        :message="error('email')"
                    />
                </div>

                <div class="mt-4">
                    <biz-form-password
                        v-model="form.password"
                        label="Password"
                        :required="true"
                    />
                </div>

                <div class="mt-4">
                    <biz-form-password
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                        label="Confirm Password"
                        :required="true"
                    />
                </div>

                <div class="mt-4">
                    <biz-button
                        class="button is-info"
                        :disabled="form.processing"
                    >
                        Reset Password
                    </biz-button>
                </div>
            </form>
        </template>
    </layout-admin>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import LayoutAdmin from '@/Pages/Auth/Admin/LayoutAdmin.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormPassword from '@/Biz/Form/Password.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizLink from '@/Biz/Link.vue';
    import { back as iconBack } from '@/Libs/icon-class';

    export default {
        components: {
            LayoutAdmin,
            BizButton,
            BizErrorNotifications,
            BizFormInput,
            BizFormPassword,
            BizIcon,
            BizLink,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        props: {
            email: {
                type: String,
                required: true,
            },
            token: {
                type: String,
                required: true,
            },
        },

        data() {
            return {
                form: this.$inertia.form({
                    token: this.token,
                    email: this.email,
                    password: '',
                    password_confirmation: '',
                    processing: true,
                }),
                iconBack,
            }
        },

        methods: {
            submit() {
                this.form.post(this.route('admin.password.update'), {
                    onStart: () => this.onStartLoadingOverlay(),
                    onFinish: () => {
                        this.form.reset('password', 'password_confirmation');
                        this.onEndLoadingOverlay();
                    },
                })
            }
        }
    }
</script>