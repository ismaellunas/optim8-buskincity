<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="mb-6">
            <form
                class="columns"
                method="post"
                @submit.prevent="onSubmit"
            >
                <div class="column">
                    <fieldset
                        class="box"
                        :disabled="isProcessing"
                    >
                        <form-user-profile
                            v-model="form"
                            :photo-url="photoUrl"
                            :language-options="supportedLanguageOptions"
                            :role-options="roleOptions"
                        />

                        <form-user-password
                            v-model="form"
                        />

                        <!-- City Selection for City Administrator role -->
                        <div v-if="isCityAdministrator" class="mb-5">
                            <h4 class="title is-5 mb-3">{{ i18n.administered_cities ?? 'Administered Cities' }}</h4>
                            <hr class="mt-0 mb-4">

                            <div class="columns">
                                <div class="column is-6">
                                    <biz-form-city-select
                                        v-model="newCityId"
                                        :label="i18n.add_city ?? 'Add City'"
                                        placeholder="Search to add a city..."
                                        @select="addCity"
                                    />
                                </div>
                            </div>

                            <div class="table-container">
                                <table class="table is-fullwidth is-striped is-hoverable">
                                    <thead>
                                        <tr>
                                            <th>{{ i18n.city ?? 'City' }}</th>
                                            <th>{{ i18n.country ?? 'Country' }}</th>
                                            <th class="has-text-right">{{ i18n.actions ?? 'Actions' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="city in selectedCities" :key="city.id">
                                            <td>{{ city.name }}</td>
                                            <td>{{ city.country_code }}</td>
                                            <td class="has-text-right">
                                                <biz-button
                                                    class="is-danger is-small"
                                                    type="button"
                                                    @click="removeCity(city)"
                                                >
                                                    {{ i18n.remove ?? 'Remove' }}
                                                </biz-button>
                                            </td>
                                        </tr>
                                        <tr v-if="selectedCities.length === 0">
                                            <td colspan="3" class="has-text-centered has-text-grey">
                                                {{ i18n.no_cities_assigned ?? 'No cities assigned.' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button-link
                                    :href="route(baseRouteName+'.index')"
                                    class="is-link is-light"
                                >
                                    {{ i18n.cancel }}
                                </biz-button-link>
                            </div>
                            <div class="control">
                                <biz-button class="is-link">
                                    {{ i18n.create }}
                                </biz-button>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormCitySelect from '@/Biz/Form/CitySelect.vue';
    import FormUserPassword from '@/Pages/User/FormPassword.vue';
    import FormUserProfile from '@/Pages/User/FormProfile.vue';
    import { useForm } from '@inertiajs/vue3';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        name: 'UserCreate',

        components: {
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            BizFormCitySelect,
            FormUserPassword,
            FormUserProfile,
        },

        mixins: [
            MixinHasLoader,
        ],

        provide() {
            return {
                i18n: this.i18n,
                instructions: this.instructions,
            };
        },

        layout: AppLayout,

        props: {
            errors: { type: Object, default: () => {} },
            supportedLanguageOptions: { type: Array, default: () => [] },
            record: {type: Object, default: () => {} },
            roleOptions: { type: Array, default: () => [] },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => ({
                cancel : 'Cancel',
                Create : 'Create',
            }) },
            instructions: { type: Object, default: () => {} },
        },

        setup(props) {
            const form = {
                first_name: null,
                last_name: null,
                email: null,
                role: null,
                password: null,
                password_confirmation: null,
                country_code: null,
                language_id: props.record.language_id,
                photo: null,
                is_photo_deleted: false,
                cities: [],
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                baseRouteName: 'admin.users',
                photoUrl: null,
                isProcessing: false,
                selectedCities: [],
                newCityId: null,
            };
        },

        computed: {
            isCityAdministrator() {
                // Find the City Administrator role in roleOptions
                const cityAdminRole = this.roleOptions.find(r => r.value === 'City Administrator');
                return cityAdminRole && this.form.role === cityAdminRole.id;
            },
        },

        watch: {
            'form.role'() {
                // Clear selected cities when role changes away from city administrator
                if (!this.isCityAdministrator) {
                    this.selectedCities = [];
                    this.form.cities = [];
                }
            },
        },

        methods: {
            addCity(city) {
                if (!this.selectedCities.find(c => c.id === city.id)) {
                    this.selectedCities.push(city);
                    this.form.cities = this.selectedCities.map(c => c.id);
                }
                this.newCityId = null;
            },

            removeCity(city) {
                this.selectedCities = this.selectedCities.filter(c => c.id !== city.id);
                this.form.cities = this.selectedCities.map(c => c.id);
            },

            onSubmit() {
                const self = this;
                const form = this.form;
                form.post(route(this.baseRouteName+'.store'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.onStartLoadingOverlay();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        form.isDirty = false;
                        form.reset();

                        self.photoUrl = null;
                    },
                    onFinish: () => {
                        self.onEndLoadingOverlay();
                        self.isProcessing = false;
                    }
                });
            },
        },
    };
</script>
