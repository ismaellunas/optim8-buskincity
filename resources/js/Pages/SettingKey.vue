<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="box mb-6">
            <form @submit.prevent="onSubmit">
                <div class="field is-grouped is-grouped-right">
                    <div class="control">
                        <biz-button class="is-link">
                            {{ i18n.save }}
                        </biz-button>
                    </div>
                </div>

                <fieldset :disabled="isProcessing">
                    <template
                        v-for="(group, index) in groups"
                        :key="index"
                    >
                        <div
                            v-for="keyInput in keys[group]"
                            :key="keyInput.key"
                            class="columns"
                        >
                            <div class="column is-half">
                                <h3>{{ keyInput.display_name }}</h3>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <p class="control">
                                        <biz-input
                                            v-model="form[keyInput.key]"
                                        />
                                    </p>
                                </div>
                                <p
                                    v-if="form.errors?.default && form.errors.default[keyInput.key]"
                                >
                                    <biz-input-error
                                        :message="error(keyInput.key)"
                                    />
                                </p>
                            </div>
                        </div>

                        <hr
                            v-if="!(index == (groups.length - 1))"
                        >
                    </template>
                </fieldset>
            </form>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizInput from '@/Biz/Input.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';
    import { forEach } from 'lodash';

    export default {
        name: 'SettingKey',

        components: {
            BizButton,
            BizErrorNotifications,
            BizInput,
            BizInputError,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: {type: String, required: true},
            groups: {type: Array, required: true},
            keys: {type: Object, required: true},
            title: {type: String, required: true},
            i18n: {type: Object, default: () => ({
                save: 'Save',
            })},
        },

        setup(props) {
            let form = {};

            forEach(props.groups, function (group) {
                forEach(props.keys[group], function (key) {
                    form[key.key] = key.value;
                });
            });

            return {
                form: useForm(form)
            };
        },

        data() {
            return {
                isProcessing: false,
            };
        },

        methods: {
            onSubmit() {
                const self = this;

                self.form.post(
                    route(self.baseRouteName + '.update'),
                    {
                        onStart: () => {
                            self.isProcessing = true;
                            self.onStartLoadingOverlay();
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                        },
                        onFinish: () => {
                            self.isProcessing = false;
                            self.onEndLoadingOverlay();

                            setTimeout(() => {
                                location.reload();
                            }, 200);
                        },
                    }
                )
            }
        },
    };
</script>
