<template>
    <div class="box mb-6">
        <biz-provide-inject-tabs
            v-model="activeTab"
            class="is-boxed"
        >
            <biz-provide-inject-tab title="Email">
                <form
                    method="post"
                    @submit.prevent="submit"
                >
                    <biz-form-text-editor
                        v-model="form.email_new_booking"
                        height="200"
                        label="New Booking"
                        mode="email"
                    />
                    <biz-form-text-editor
                        v-model="form.email_reminder"
                        height="200"
                        label="Booking Reminder"
                        mode="email"
                    />
                    <biz-form-text-editor
                        v-model="form.email_cancellation"
                        height="200"
                        label="Booking Cancellation"
                        mode="email"
                    />

                    <div class="field is-grouped is-grouped-right mt-4">
                        <div class="control">
                            <biz-button class="is-link">
                                Save
                            </biz-button>
                        </div>
                    </div>
                </form>
            </biz-provide-inject-tab>

            <biz-provide-inject-tab title="Check In">
                <form
                    method="post"
                    @submit.prevent="submit"
                >
                    <div class="columns">
                        <div class="column is-6">
                            <biz-form-select
                                v-model="form.allowed_early_check_in"
                                label="Available Check In before"
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
                                label="Check-in radius"
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
                                Save
                            </biz-button>
                        </div>
                    </div>
                </form>
            </biz-provide-inject-tab>
        </biz-provide-inject-tabs>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizSelect from '@/Biz/Select';
    import BizFormNumberAddons from '@/Biz/Form/NumberAddons';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizFormTextEditor from '@/Biz/Form/TextEditor';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasTab from '@/Mixins/HasTab';
    import { confirmLeaveProgress, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { ref } from 'vue';

    export default {
        components: {
            BizButton,
            BizSelect,
            BizFormSelect,
            BizFormTextEditor,
            BizFormNumberAddons,
            BizProvideInjectTab,
            BizProvideInjectTabs,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
            MixinHasTab,
        ],

        layout: AppLayout,

        props: {
            bookingSettings: { type: Object, required: true },
            title: { type: String, default: "Settings" },
        },

        setup(props) {
            const form = {
                email_new_booking: props.bookingSettings?.booking_email_new_booking ?? "",
                email_reminder: props.bookingSettings?.booking_email_reminder ?? "",
                email_cancellation: props.bookingSettings?.booking_email_cancellation ?? "",
                allowed_early_check_in: props.bookingSettings?.allowed_early_check_in ?? 0,
                check_in_radius: props.bookingSettings?.check_in_radius ?? {value:null,unit:'m'},
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
        }
    };
</script>
