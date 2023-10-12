<template>
    <div class="box">
        <edit-tabs
            :tabs="tabs"
            :tab-index="activeTab"
        />

        <form @submit.prevent="submitGeneral">
            <div class="columns">
                <div class="column is-half">
                    <biz-form-input
                        v-model="generalForm.title"
                        :label="i18n.title ?? 'Title'"
                        required
                        maxlength="32"
                        :message="error('title')"
                    />
                </div>
            </div>

            <div class="columns mt-4">
                <div class="column is-5">
                    <biz-button
                        v-if="record.is_active"
                        class="is-danger"
                        type="button"
                        @click="confirmActivation(action.deactivate)"
                    >
                        {{ i18n.deactivate }}
                    </biz-button>

                    <biz-button
                        v-if="!record.is_active"
                        class="is-primary"
                        type="button"
                        @click="confirmActivation(action.activate)"
                    >
                        {{ i18n.activate }}
                    </biz-button>
                </div>

                <div class="column is-5">
                    <div class="buttons is-right">
                        <biz-button-link
                            :href="route(baseRouteName+'index')"
                        >
                            {{ i18n.cancel }}
                        </biz-button-link>

                        <biz-button class="is-link">
                            {{ i18n.update }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors.js';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import EditTabs from './EditTabs.vue';
    import { computed, ref, onMounted } from 'vue';
    import { confirmDelete, confirm, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { activationAlertConfigs } from '@/Libs/module.js';
    import { router, useForm, usePage } from '@inertiajs/vue3';

    export default {
        name: 'ModuleEdit',

        components: {
            BizButton,
            BizButtonLink,
            BizFormInput,
            EditTabs,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        layout: AppLayout,

        props: {
            tabs: { type: Array, required: true },
            tabIndex: { type: Number, default: 0 },
            record: { type: Object, required: true },
            baseRouteName: { type: String, required: true },
        },

        setup(props) {
            const generalForm = useForm({ title: '' });

            onMounted(() => {
                generalForm.title = props.record.title;
            });

            return {
                activeTab: props.tabIndex,
                generalForm,
                i18n: usePage().props.i18n,
                action: {
                    activate: 'activate',
                    deactivate: 'deactivate',
                }
            };
        },

        methods: {
            submitGeneral() {
                this.generalForm.put(route(this.baseRouteName+'edit', {id: this.record.id}), {
                    onStart: this.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: this.onEndLoadingOverlay,
                });
            },

            async confirmActivation(action) {
                const configs = await activationAlertConfigs(route(
                    this.baseRouteName + 'confirm-' + action,
                    { id: this.record.id }
                ));

                confirm(null, null, "Yes", configs).then((result) => {
                    if (result.isConfirmed) {
                        this.activation(action);
                    }
                })
            },

            activation(action) {
                const url = route(this.baseRouteName + action, {id: this.record.id});

                router.post(url, {}, {
                    onStart: this.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: this.onEndLoadingOverlay,
                });
            },
        }
    };
</script>
