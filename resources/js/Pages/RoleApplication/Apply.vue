<template>
    <div class="container py-6">
        <div class="box" style="max-width: 720px; margin: 0 auto;">
            <h1 class="title is-4">{{ title }}</h1>
            <p class="mb-4">{{ roleLabel }}</p>

            <biz-error-notifications :errors="$page.props.errors" />

            <form @submit.prevent="onSubmit">
                <biz-form-input
                    v-model="form.email"
                    label="Email"
                    type="email"
                    required
                    :message="form.errors.email"
                />
                <biz-form-input
                    v-model="form.first_name"
                    label="First name"
                    required
                    :message="form.errors.first_name"
                />
                <biz-form-input
                    v-model="form.last_name"
                    label="Last name"
                    required
                    :message="form.errors.last_name"
                />
                <biz-form-select
                    v-model="form.city_id"
                    class="is-fullwidth"
                    label="City"
                    required
                    :message="form.errors.city_id"
                >
                    <option :value="null" disabled>Select city</option>
                    <option
                        v-for="city in cityOptions"
                        :key="city.id"
                        :value="city.id"
                    >
                        {{ city.value }}
                    </option>
                </biz-form-select>
                <biz-form-textarea
                    v-model="form.excerpt"
                    label="Short description"
                    :message="form.errors.excerpt"
                />
                <biz-form-textarea
                    v-model="form.description"
                    label="Description"
                    :message="form.errors.description"
                />

                <div class="field">
                    <label class="label">Logo</label>
                    <input
                        type="file"
                        accept="image/*"
                        @change="onLogoChange"
                    >
                    <p v-if="form.errors.logo" class="help is-danger">{{ form.errors.logo }}</p>
                </div>

                <div class="field">
                    <label class="label">Cover / banner</label>
                    <input
                        type="file"
                        accept="image/*"
                        @change="onCoverChange"
                    >
                    <p v-if="form.errors.cover" class="help is-danger">{{ form.errors.cover }}</p>
                </div>

                <biz-recaptcha
                    v-if="recaptchaSiteKey"
                    ref="recaptcha"
                    action="role_application"
                    :site-key="recaptchaSiteKey"
                    @on-verify="recaptchaVerify"
                />

                <biz-button class="is-link mt-4" type="submit">
                    Submit application
                </biz-button>
            </form>
        </div>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import BizRecaptcha from '@/Biz/Recaptcha.vue';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            BizButton,
            BizErrorNotifications,
            BizFormInput,
            BizFormSelect,
            BizFormTextarea,
            BizRecaptcha,
        },

        props: {
            requestedRole: { type: String, required: true },
            roleLabel: { type: String, required: true },
            cityOptions: { type: Array, default: () => [] },
            recaptchaSiteKey: { type: String, default: null },
            defaults: { type: Object, default: () => ({}) },
            title: { type: String, required: true },
        },

        setup(props) {
            return {
                form: useForm({
                    email: props.defaults.email ?? '',
                    first_name: props.defaults.first_name ?? '',
                    last_name: props.defaults.last_name ?? '',
                    requested_role: props.requestedRole,
                    city_id: null,
                    description: '',
                    excerpt: '',
                    logo: null,
                    cover: null,
                }),
            };
        },

        methods: {
            onLogoChange(event) {
                this.form.logo = event.target.files[0] ?? null;
            },
            onCoverChange(event) {
                this.form.cover = event.target.files[0] ?? null;
            },
            onSubmit() {
                if (this.recaptchaSiteKey) {
                    this.$refs.recaptcha.execute();
                    return;
                }
                this.submitForm(null);
            },
            recaptchaVerify(response = null) {
                this.submitForm(response);
            },
            submitForm(recaptchaResponse) {
                this.form
                    .transform((data) => ({
                        ...data,
                        'g-recaptcha-response': recaptchaResponse,
                    }))
                    .post(route('role-applications.store'), {
                        forceFormData: true,
                    });
            },
        },
    };
</script>
