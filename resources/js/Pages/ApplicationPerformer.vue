<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <biz-error-notifications :errors="$page.props.errors" />

        <div class="box mb-6">
            <form @submit.prevent="submit">
                <div class="columns is-multiline">
                    <div class="column is-half">
                        <biz-form-input
                            id="first_name"
                            v-model="form.first_name"
                            label="First name"
                            maxlength="128"
                            placeholder="Your First Name"
                            required
                            :message="error('first_name')"
                        />
                    </div>
                    <div class="column is-half">
                        <biz-form-input
                            id="last_name"
                            v-model="form.last_name"
                            label="Last name"
                            maxlength="128"
                            placeholder="Your Last Name"
                            required
                            :message="error('first_name')"
                        />
                    </div>

                    <div class="column is-full">
                        <biz-form-input
                            id="company"
                            v-model="form.company"
                            label="Company (optional)"
                            maxlength="128"
                            placeholder="Your Company Name"
                            :message="error('company')"
                        />
                    </div>
                    <div class="column is-half">
                        <biz-form-input
                            id="email"
                            v-model="form.email"
                            label="Email"
                            maxlength="255"
                            placeholder="Your Email Address"
                            required
                            :message="error('email')"
                        />
                    </div>
                    <div class="column is-half">
                        <biz-form-phone
                            id="phone"
                            v-model="form.phone"
                            label="Phone"
                            maxlength="20"
                            placeholder="Your Phone Number"
                            required
                            :message="error('phone.number')"
                            :country-options="phoneCountryOptions"
                            :default-country="defaultCountry"
                        />
                    </div>

                    <div class="column is-half">
                        <biz-form-input
                            v-model="form.stage_name"
                            label="Stage name"
                            maxlength="64"
                            placeholder="Your Stage Name"
                            required
                            :message="error('stage_name')"
                        />
                    </div>
                    <div class="column is-half">
                        <biz-form-select
                            v-model="form.discipline"
                            label="Discipline"
                            maxlength="64"
                            placeholder="Choose One"
                            required
                            :message="error('discipline')"
                        >
                            <option
                                v-for="option in disciplineOptions"
                                :key="option.id"
                                :value="option.id"
                            >
                                {{ option.value }}
                            </option>
                        </biz-form-select>
                    </div>
                </div>

                <div class="columns is-multiline">
                    <div class="column pb-0">
                        <biz-label>
                            Address
                        </biz-label>
                    </div>

                    <div class="column is-full">
                        <biz-form-input
                            id="address"
                            ref="address"
                            v-model="form.address"
                            label="Street Address"
                            maxlength="128"
                            required
                            :message="error('stage_name')"
                        />
                    </div>

                    <div class="column is-half">
                        <biz-form-input
                            id="city"
                            v-model="form.city"
                            label="City"
                            maxlength="64"
                            required
                            :message="error('stage_name')"
                        />
                    </div>
                    <div class="column is-half">
                        <biz-form-input
                            id="postal_code"
                            v-model="form.postal_code"
                            label="ZIP / Postal Code"
                            maxlength="32"
                            required
                            :message="error('postal_code')"
                        />
                    </div>

                    <div class="column is-full">
                        <biz-form-select
                            id="country"
                            v-model="form.country"
                            label="Country"
                            maxlength="2"
                            placeholder="Choose One"
                            required
                            :message="error('country')"
                        >
                            <option
                                v-for="option in countryOptions"
                                :key="option.id"
                                :value="option.id"
                            >
                                {{ option.value }}
                            </option>
                        </biz-form-select>
                    </div>
                </div>

                <div class="columns is-multiline">
                    <div class="column is-full">
                        <biz-form-textarea
                            id="about_you"
                            v-model="form.about_you"
                            label="Tell us about you"
                            required
                            rows="3"
                            :message="error('about_you')"
                        />
                    </div>

                    <div class="column is-full">
                        <biz-form-textarea
                            id="performance_description"
                            v-model="form.performance_description"
                            label="Describe your performance"
                            required
                            rows="3"
                            :message="error('performance_description')"
                        />
                    </div>
                </div>

                <div class="columns is-multiline">
                    <div class="column is-full pb-0">
                        <biz-label>
                            Fees per day
                        </biz-label>
                    </div>

                    <div class="column is-one-third">
                        <biz-form-input
                            id="fees_per_day_corporate_gigs"
                            v-model="form.fees_per_day_corporate_gigs"
                            label="Corporate gigs"
                            placeholder="ex: $250"
                            required
                            :message="error('fees_per_day_corporate_gigs')"
                        />
                    </div>
                    <div class="column is-one-third">
                        <biz-form-input
                            id="fees_per_day_private_gigs"
                            v-model="form.fees_per_day_private_gigs"
                            label="Private gigs"
                            placeholder="ex: $250"
                            required
                            :message="error('fees_per_day_private_gigs')"
                        />
                    </div>
                    <div class="column is-one-third">
                        <biz-form-input
                            id="fees_per_day_festival_gigs"
                            v-model="form.fees_per_day_festival_gigs"
                            label="Festival gigs"
                            placeholder="ex: $250"
                            required
                            :message="error('fees_per_day_festival_gigs')"
                        />
                    </div>
                </div>

                <div class="columns is-multiline">
                    <div class="column is-one-third">
                        <biz-form-input
                            id="facebook"
                            v-model="form.facebook"
                            label="Facebook"
                            placeholder="Facebook Link"
                            :message="error('facebook')"
                        />
                    </div>
                    <div class="column is-one-third">
                        <biz-form-input
                            id="twitter"
                            v-model="form.twitter"
                            label="Twitter"
                            placeholder="Twitter Link"
                            :message="error('twitter')"
                        />
                    </div>
                    <div class="column is-one-third">
                        <biz-form-input
                            v-model="form.instagram"
                            label="Instagram"
                            placeholder="Instagram Link"
                            :message="error('instagram')"
                        />
                    </div>
                </div>

                <div class="columns is-multiline">
                    <div class="column is-half">
                        <biz-form-input
                            id="youtube"
                            v-model="form.youtube"
                            label="Youtube"
                            placeholder="Youtube Link"
                            :message="error('youtube')"
                        />
                    </div>
                    <div class="column is-half">
                        <biz-form-input
                            id="other_links"
                            v-model="form.other_links"
                            label="Other(s)"
                            placeholder="Other Links (separate by comma)"
                            :message="error('other_links') ?? error('other_links.*')"
                        />
                    </div>

                    <div class="column is-full">
                        <biz-form-input
                            id="promotional_video"
                            v-model="form.promotional_video"
                            label="Promotional Video"
                            placeholder="Youtube or Vimeo Link"
                            required
                            :message="error('promotional_video')"
                        />
                    </div>
                </div>

                <div class="field">
                    <div class="buttons is-centered">
                        <biz-button class="is-primary">
                            Submit Application
                        </biz-button>
                    </div>
                </div>
            </form>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormPhone from '@/Biz/Form/Phone';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import BizLabel from '@/Biz/Label';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        name: 'PerformerApplication',

        components: {
            AppLayout,
            BizButton,
            BizErrorNotifications,
            BizFormInput,
            BizFormPhone,
            BizFormSelect,
            BizFormTextarea,
            BizLabel,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            countryOptions: { type: Array, required: true },
            defaultCountry: { type: String, default: "" },
            disciplineOptions: { type: Array, required: true },
            title: { type: String, default: "Performer Application Form" },
            email: { type: String, default: null },
            firstName: { type: String, default: null },
            lastName: { type: String, default: null },
            phoneCountryOptions: { type: Array, required: true},
        },

        setup(props) {
            const form = {
                first_name: props.firstName,
                last_name: props.lastName,
                company: null,
                email: props.email,
                phone: {
                    country: props.defaultCountry,
                    number: null,
                },
                stage_name: null,
                discipline: null,
                address: null,
                city: null,
                postal_code: null,
                country: props.defaultCountry,
                about_you: null,
                performance_description: null,
                fees_per_day_corporate_gigs: null,
                fees_per_day_private_gigs: null,
                fees_per_day_festival_gigs: null,
                facebook: null,
                twitter: null,
                instagram: null,
                youtube: null,
                other_links: null,
                promotional_video: null,
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                loader: null,
            }
        },

        methods: {
            submit() {
                const self = this;

                this.form.post(this.route('performer-application-form.store'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError(errors) {
                        oopsAlert();
                    },
                    onFinish: () => {
                        self.loader.hide();
                    }
                });
            },
        },
    };
</script>
