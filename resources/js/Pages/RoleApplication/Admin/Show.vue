<template>
    <div>
        <div class="box">
            <biz-error-notifications :errors="$page.props.errors" />

            <div class="columns is-multiline">
                <div class="column is-half">
                    <p><strong>Applicant:</strong> {{ application.first_name }} {{ application.last_name }}</p>
                    <p><strong>Email:</strong> {{ application.email }}</p>
                    <p><strong>Role:</strong> {{ application.requested_role }}</p>
                    <p v-if="application.country_space">
                        <strong>Country:</strong> {{ application.country_space.name }}
                    </p>
                    <p><strong>City:</strong> {{ application.city?.name }}</p>
                    <p><strong>Status:</strong> {{ application.status }}</p>
                </div>
                <div class="column is-half">
                    <p v-if="application.excerpt"><strong>Excerpt:</strong> {{ application.excerpt }}</p>
                    <p v-if="application.description"><strong>Description:</strong> {{ application.description }}</p>
                    <p v-if="application.reject_reason"><strong>Reject reason:</strong> {{ application.reject_reason }}</p>
                    <p v-if="application.replaced_user">
                        <strong>Replaced admin:</strong>
                        {{ application.replaced_user.first_name }} {{ application.replaced_user.last_name }}
                        ({{ application.replaced_user.email }})
                    </p>
                </div>
            </div>

            <div
                v-if="approvalPreview?.requires_replace_confirmation"
                class="notification is-warning is-light"
            >
                <p>
                    This city already has a City Administrator:
                    <strong>{{ approvalPreview.existing_city_admin.name }}</strong>
                    ({{ approvalPreview.existing_city_admin.email }}).
                    Approving will replace them.
                </p>
                <biz-checkbox v-model:checked="confirmReplace">
                    I understand the existing City Administrator will be replaced
                </biz-checkbox>
            </div>

            <div
                v-if="can.approve"
                class="field is-grouped mt-4"
            >
                <div class="control">
                    <biz-button
                        class="is-success"
                        type="button"
                        :disabled="approvalPreview?.requires_replace_confirmation && !confirmReplace"
                        @click="approve"
                    >
                        Approve
                    </biz-button>
                </div>
            </div>

            <div
                v-if="can.reject"
                class="mt-4"
            >
                <biz-form-textarea
                    v-model="rejectReason"
                    label="Reject reason"
                />
                <biz-button
                    class="is-danger mt-2"
                    type="button"
                    @click="reject"
                >
                    Reject
                </biz-button>
            </div>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import { useForm } from '@inertiajs/vue3';
    import { ref } from 'vue';

    export default {
        components: {
            BizButton,
            BizCheckbox,
            BizErrorNotifications,
            BizFormTextarea,
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            application: { type: Object, required: true },
            approvalPreview: { type: Object, default: null },
            can: { type: Object, default: () => ({}) },
            title: { type: String, required: true },
        },

        setup(props) {
            const approveForm = useForm({ confirm_replace: false });
            const rejectForm = useForm({ reject_reason: '' });

            return {
                confirmReplace: ref(false),
                rejectReason: ref(''),
                approveForm,
                rejectForm,
            };
        },

        methods: {
            approve() {
                this.approveForm.confirm_replace = this.confirmReplace;
                this.approveForm.post(route(this.baseRouteName + '.approve', this.application.id));
            },
            reject() {
                this.rejectForm.reject_reason = this.rejectReason;
                this.rejectForm.post(route(this.baseRouteName + '.reject', this.application.id));
            },
        },
    };
</script>
