<template>
    <layout-admin>
        <template #back>
            <a @click.prevent="back">
                <span class="icon">
                    <i class="fas fa-arrow-left" />
                </span>
                <span>Back</span>
            </a>
        </template>

        <template #default>
            <h1 class="title has-text-centered">
                Welcome Back
            </h1>
            <h2 class="subtitle has-text-centered">
                <span>Lorem ipsum dolor sit amet.</span>
            </h2>
            <div class="has-text-left">
                <biz-error-notifications :errors="$page.props.errors" />

                <biz-flash-notifications :flash="$page.props.flash" />

                <form @submit.prevent="submit">
                    <biz-form-input
                        v-model="form.email"
                        label="Email"
                        type="email"
                        placeholder="Enter your email"
                        required
                        autofocus
                    />

                    <biz-form-password
                        v-model="form.password"
                        autocomplete="current-password"
                        label="Password"
                        placeholder="Enter your password"
                        :required="true"
                    />

                    <div class="field columns">
                        <div class="column has-text-left">
                            <biz-checkbox
                                name="remember"
                                v-model:checked="form.remember"
                            >
                                <span class="pl-1">Remember me</span>
                            </biz-checkbox>
                        </div>
                        <div class="column has-text-right">
                            <biz-link
                                v-if="canResetPassword"
                                :href="route('admin.password.request')"
                            >
                                Forgot your password?
                            </biz-link>
                        </div>
                    </div>

                    <biz-button
                        class="is-block is-info is-fullwidth"
                    >
                        Log In <i class="fas fa-sign-in-alt" />
                    </biz-button>
                </form>
            </div>
        </template>
    </layout-admin>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFlashNotifications from '@/Biz/FlashNotifications';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormPassword from '@/Biz/Form/Password';
    import BizLink from '@/Biz/Link';
    import LayoutAdmin from '@/Pages/Auth/Admin/LayoutAdmin';

    export default {
        components: {
            BizButton,
            BizCheckbox,
            BizErrorNotifications,
            BizFlashNotifications,
            BizFormInput,
            BizFormPassword,
            BizLink,
            LayoutAdmin,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            canResetPassword: Boolean,
            status: {type: String, default: ""},
        },

        data() {
            return {
                canLogin: true,
                canRegister: true,
                form: this.$inertia.form({
                    email: '',
                    password: '',
                    remember: false
                })
            }
        },

        methods: {
            submit() {
                this.form
                    .transform(data => ({
                        ... data,
                        remember: this.form.remember ? 'on' : ''
                    }))
                    .post(this.route('admin.login'), {
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => {
                            this.form.reset('password');
                            this.onEndLoadingOverlay()
                        },
                    })
            },
            back() {
                window.location = "/";
            },
        }
    }
</script>
