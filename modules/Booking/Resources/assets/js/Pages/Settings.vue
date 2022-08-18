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
        </biz-provide-inject-tabs>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizFormTextEditor from '@/Biz/Form/TextEditor';
    import BizFormNumberAddons from '@/Biz/Form/NumberAddons';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasTab from '@/Mixins/HasTab';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { confirmLeaveProgress, oops as oopsAlert, success as successAlert } from '@/Libs/alert';

    export default {
        components: {
            BizButton,
            BizFormTextEditor,
            BizProvideInjectTab,
            BizProvideInjectTabs,
        },

        mixins: [
            MixinHasLoader,
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
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                activeTab: 0,
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
