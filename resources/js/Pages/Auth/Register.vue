<template>
    <section class="hero is-fullheight">
        <div class="hero-body">
            <div class="container has-text-centered">
                <div class="columns">
                    <div class="column is-two-fifths has-text-left">
                        <div class="card">
                            <div class="card-image">
                                <figure class="image is-3by4">
                                    <img src="https://dummyimage.com/550x715/e5e5e5/ffffff.jpg">
                                </figure>
                            </div>
                        </div>
                    </div>
                    <div class="column is-three-fifths has-text-left">
                        <div class="level">
                            <div class="level-left">
                                <div class="level-item">
                                    <a @click.prevent="backOrOpenSocialList">
                                        <span class="icon"><i class="fas fa-arrow-left" /></span>
                                        <span>Back</span>
                                    </a>
                                </div>
                            </div>
                            <div class="level-right">
                                <div class="level-item">
                                    <span class="mr-3">
                                        Already have an account?
                                    </span>
                                    <biz-button-link :href="route('login')">
                                        Login
                                    </biz-button-link>
                                </div>
                            </div>
                        </div>
                        <section class="section">
                            <div
                                class="columns"
                                :class="{'is-hidden': !isSocialMediaLogin}"
                            >
                                <div class="column is-9 is-offset-1">
                                    <h1 class="title">
                                        Sign Up
                                    </h1>
                                    <h2 class="subtitle">
                                        <span>Are you performer? </span>
                                        <span>Sign Up Here</span>
                                    </h2>
                                    <div class="has-text-centered">
                                        <biz-social-media-list />

                                        <div class="h-line-wrapper">
                                            <span class="h-line-words">or</span>
                                        </div>
                                        <a
                                            class="box"
                                            @click.prevent="toggleIsSocialMediaLogin"
                                        >
                                            <i class="fas fa-envelope" /> Continue with <b>Email</b>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="columns"
                                :class="{'is-hidden': isSocialMediaLogin}"
                            >
                                <div class="column is-9 is-offset-1">
                                    <h1 class="title">
                                        Create Account
                                    </h1>
                                    <h2 class="subtitle">
                                        Lorem ipsum dolor sit amet.
                                    </h2>

                                    <biz-error-notifications :errors="$page.props.errors" />

                                    <div class="has-text-left">
                                        <form @submit.prevent="submit">
                                            <biz-form-input
                                                v-model="form.first_name"
                                                label="First Name"
                                                required
                                                autofocus
                                                :message="error('first_name')"
                                            />

                                            <biz-form-input
                                                v-model="form.last_name"
                                                label="Last Name"
                                                required
                                                :message="error('last_name')"
                                            />

                                            <biz-form-input
                                                v-model="form.email"
                                                label="Email"
                                                required
                                                type="email"
                                                :message="error('email')"
                                            />

                                            <biz-form-dropdown-search
                                                label="Country"
                                                :close-on-click="true"
                                                :message="error('country_code')"
                                                @search="searchCountry($event)"
                                            >
                                                <template #trigger>
                                                    <span :style="{'min-width': '4rem'}">
                                                        {{ selectedCountry }}
                                                    </span>
                                                </template>

                                                <biz-dropdown-item
                                                    v-for="option in filteredCountries"
                                                    :key="option.id"
                                                    @click="selectedCountry = option"
                                                >
                                                    {{ option.value }}
                                                </biz-dropdown-item>
                                            </biz-form-dropdown-search>

                                            <biz-form-dropdown-search
                                                label="Language"
                                                :close-on-click="true"
                                                :message="error('language_id')"
                                                @search="searchLanguage($event)"
                                            >
                                                <template #trigger>
                                                    <span :style="{'min-width': '4rem'}">
                                                        {{ selectedLanguage }}
                                                    </span>
                                                </template>

                                                <biz-dropdown-item
                                                    v-for="option in filteredLanguages"
                                                    :key="option.id"
                                                    @click="selectedLanguage = option"
                                                >
                                                    {{ option.value }}
                                                </biz-dropdown-item>
                                            </biz-form-dropdown-search>

                                            <biz-form-password
                                                v-model="form.password"
                                                label="Password"
                                                required
                                                :message="error('password')"
                                            />

                                            <biz-form-password
                                                v-model="form.password_confirmation"
                                                autocomplete="new-password"
                                                label="Confirm Password"
                                                required
                                                :message="error('password_confirmation')"
                                            />

                                            <div class="flex mt-4">
                                                <div class="columns is-gapless">
                                                    <div class="column is-two-thirds">
                                                        <span>
                                                            By clicking on <b>Create Account</b> you agree with our Terms and Conditions
                                                        </span>
                                                    </div>
                                                    <div class="column is-one-third has-text-right">
                                                        <biz-button
                                                            class="button is-info"
                                                            :disabled="form.processing"
                                                        >
                                                            Create Account
                                                        </biz-button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormPassword from '@/Biz/Form/Password';
    import BizSocialMediaList from '@/Biz/SocialMediaList';
    import { find, debounce, isEmpty, filter } from 'lodash';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButtonLink,
            BizSocialMediaList,
            BizFormInput,
            BizFormPassword,
            BizErrorNotifications,
            BizFormDropdownSearch,
            BizDropdownItem,
            BizButton
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            userLocation: {
                type: Object,
                required: true,
            },
        },

        setup() {
            return {
                countryOptions: usePage().props.value.countryOptions,
                languageOptions: usePage().props.value.shownLanguageOptions,
            };
        },

        data() {
            return {
                isSocialMediaLogin: true,
                form: this.$inertia.form({
                    first_name: '',
                    last_name: '',
                    email: '',
                    password: '',
                    password_confirmation: '',
                    country_code: this.userLocation.country.code,
                    language_id: '',
                    terms: true,
                }),
                filteredCountries: this.countryOptions.slice(0, 10),
                filteredLanguages: this.languageOptions.slice(0, 10),
            }
        },

        computed: {
            selectedCountry: {
                get() {
                    if (this.form.country_code) {
                        let country = find(
                            this.countryOptions,
                            ['id', this.form.country_code]
                        );
                        return country.value;
                    }
                    return '';
                },
                set(country) {
                    this.form.country_code = country.id;
                }
            },

            selectedLanguage: {
                get() {
                    if (this.form.language_id) {
                        let language = find(
                            this.languageOptions,
                            ['id', parseInt(this.form.language_id)]
                        );
                        return language.value;
                    }
                    return '';
                },
                set(language) {
                    this.form.language_id = language.id;
                }
            },
        },

        mounted() {
            this.setLanguage();
        },

        methods: {
            submit() {
                this.form.post(this.route('register'), {
                    onFinish: () => this.form.reset('password', 'password_confirmation'),
                })
            },

            toggleIsSocialMediaLogin() {
                this.isSocialMediaLogin = !this.isSocialMediaLogin;
            },

            backOrOpenSocialList() {
                if (!this.isSocialMediaLogin) {
                    this.toggleIsSocialMediaLogin();
                } else {
                    this.$inertia.get('/login');
                }
            },

            setLanguage() {
                let filteredLanguage = find(
                    this.languageOptions,
                    ['code', this.userLocation.language.code]
                );

                if (filteredLanguage) {
                    this.form.language_id = filteredLanguage.id;
                }
            },

            searchCountry: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredCountries = filter(this.countryOptions, function (country) {
                        return new RegExp(term, 'i').test(country.value);
                    }).slice(0, 10);
                } else {
                    this.filteredCountries = this.countryOptions.slice(0, 10);
                }
            }, 750),

            searchLanguage: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredLanguages = filter(this.languageOptions, function (language) {
                        return new RegExp(term, 'i').test(language.value);
                    }).slice(0, 10);
                } else {
                    this.filteredLanguages = this.languageOptions.slice(0, 10);
                }
            }, 750),
        }
    }
</script>
