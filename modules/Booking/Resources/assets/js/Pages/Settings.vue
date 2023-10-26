<template>
    <div class="box mb-6">
        <biz-provide-inject-tabs
            v-model="activeTab"
            class="is-boxed"
        >
            <biz-provide-inject-tab
                :title="i18n.email"
                tab-id="email-tab-trigger"
            >
                <form
                    id="email-form"
                    method="post"
                    @submit.prevent="submit"
                >
                    <biz-form-text-editor
                        v-model="form.email_new_booking"
                        height="200"
                        :label="i18n.new_booking"
                        mode="email"
                    />
                    <biz-form-text-editor
                        v-model="form.email_reminder"
                        height="200"
                        :label="i18n.booking_remainder"
                        mode="email"
                    />
                    <biz-form-text-editor
                        v-model="form.email_cancellation"
                        height="200"
                        :label="i18n.booking_cancellation"
                        mode="email"
                    />

                    <div class="field is-grouped is-grouped-right mt-4">
                        <div class="control">
                            <biz-button class="is-link">
                                {{ i18n.save }}
                            </biz-button>
                        </div>
                    </div>
                </form>
            </biz-provide-inject-tab>

            <biz-provide-inject-tab
                :title="i18n.check_in"
                tab-id="check-in-tab-trigger"
            >
                <form
                    id="check-in-form"
                    method="post"
                    @submit.prevent="submit"
                >
                    <div class="columns">
                        <div class="column is-6">
                            <biz-form-select
                                v-model="form.allowed_early_check_in"
                                :label="i18n.available_check_in"
                                :message="error('duration')"
                                has-addons
                                is-fullwidth
                            >
                                <option
                                    v-for="checkInTime in earlyCheckInOptions"
                                    :key="checkInTime"
                                    :value="checkInTime"
                                >
                                    {{ checkInTime }}
                                </option>

                                <template #afterInput>
                                    <p class="control">
                                        <a class="button is-static">
                                            minute(s)
                                        </a>
                                    </p>
                                </template>
                            </biz-form-select>
                        </div>

                        <div class="column is-6">
                            <biz-form-number-addons
                                v-model="form.check_in_radius.value"
                                :label="i18n.check_in_radius"
                                max="1000"
                                min="0"
                                :message="error('bookable_date_range')"
                            >
                                <template #afterInput>
                                    <p class="control">
                                        <span class="select">
                                            <biz-select
                                                v-model="form.check_in_radius.unit"
                                            >
                                                <option
                                                    v-for="unit in distanceOptions"
                                                    :key="unit.id"
                                                    :value="unit.id"
                                                >
                                                    {{ unit.value }}
                                                </option>
                                            </biz-select>
                                        </span>
                                    </p>
                                </template>
                            </biz-form-number-addons>
                        </div>
                    </div>

                    <div class="field is-grouped is-grouped-right mt-4">
                        <div class="control">
                            <biz-button class="is-link">
                                {{ i18n.save }}
                            </biz-button>
                        </div>
                    </div>
                </form>
            </biz-provide-inject-tab>

            <biz-provide-inject-tab
                :title="startCase(i18n.control_access)"
                tab-id="access-tab-trigger"
            >
                <setting-access
                    v-model="form"
                    :role-options="roleOptions"
                    @submit="submit"
                />
            </biz-provide-inject-tab>
        </biz-provide-inject-tabs>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasTab from '@/Mixins/HasTab';
    import MixinHasTranslation from '@/Mixins/HasTranslation';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizFormNumberAddons from '@/Biz/Form/NumberAddons.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormTextEditor from '@/Biz/Form/TextEditor.vue';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab.vue';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs.vue';
    import BizSelect from '@/Biz/Select.vue';
    import SettingAccess from './SettingAccess.vue';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { ref, computed } from 'vue';
    import { startCase } from 'lodash';
    import { useForm } from '@inertiajs/vue3';


    export default {
        components: {
            BizButton,
            BizFormNumberAddons,
            BizFormSelect,
            BizFormTextEditor,
            BizProvideInjectTab,
            BizProvideInjectTabs,
            BizSelect,
            SettingAccess,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
            MixinHasTab,
            MixinHasTranslation,
        ],

        layout: AppLayout,

        props: {
            bookingSettings: { type: Object, required: true },
            roleOptions: { type: Array, default: () => [] },
            title: { type: String, default: "Settings" },
        },

        setup(props) {
            const bookingSettings = computed(() => props.bookingSettings);

            const form = {
                email_new_booking: bookingSettings.value?.booking_email_new_booking ?? "",
                email_reminder: bookingSettings.value?.booking_email_reminder ?? "",
                email_cancellation: bookingSettings.value?.booking_email_cancellation ?? "",
                allowed_early_check_in: bookingSettings.value?.allowed_early_check_in ?? 0,
                check_in_radius: bookingSettings.value?.check_in_radius ?? {value:null,unit:'m'},
                access_common_user: bookingSettings.value?.booking_access_common_user ?? false,
                access_roles: bookingSettings.value?.booking_access_roles ?? [],
            };

            return {
                form: useForm(form),
                earlyCheckInOptions:[0, 5, 15, 30, 45, 60],
                distanceOptions: [
                    {id: 'm', value: 'meters'},
                    {id: 'km', value: 'kilometers'},
                ],
                activeTab: ref(0),
            };
        },

        methods: {
            submit() {
                const self = this;
                const url = route('admin.booking.settings.update');

                this.form.post(url, {
                    replace: true,
                    onStart: self.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: self.onEndLoadingOverlay
                });
            },

            startCase,
        }
    };
</script>
