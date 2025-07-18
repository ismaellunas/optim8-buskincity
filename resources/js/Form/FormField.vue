<template>
    <form
        v-if="form"
        :key="formFieldKey"
        @submit.prevent="submit"
    >
        <field-group
            v-for="(group, index) in formField.fieldGroups"
            :key="index"
            v-model="form"
            :group="group"
            :selected-locale="selectedLocale"
        />

        <slot
            v-if="!hideButtons"
            name="buttons"
        >
            <div class="field">
                <biz-button class="is-medium is-primary">
                    <span class="has-text-weight-bold">
                        {{ buttonText }}
                    </span>
                </biz-button>
            </div>
        </slot>
    </form>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button.vue';
    import FieldGroup from './FieldGroup.vue';
    import { useForm, usePage } from '@inertiajs/vue3';
    import { isEmpty, forOwn } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';

    export default {
        name: 'FormField',

        components: {
            BizButton,
            FieldGroup,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            entityId: {type: Number, default: null},
            formField: { type: Object, required: true },
            localeOptions: { type: Object, default: () => {} },
            routeName: { type: String, required: true },
            routeSave: { type: String, default: 'forms.save' },
            selectedLocale: { type: String, required: true },
            hideButtons: { type: Boolean, default: false },
        },

        emits: [
            'on-success-submit'
        ],

        data() {
            return {
                form: null,
                formFieldKey: 0,
            };
        },

        computed: {
            buttonText() {
                return this.formField?.button?.text ?? 'Submit';
            },
        },

        watch: {
            formField: {
                handler(newVal) {
                    this.form = this.createForm();
                },
                immediate: true,
            },
        },

        methods: {
            createForm() {
                let fieldValue = null;
                const form = {
                    id: this.entityId,
                    _token: usePage().props.csrfToken,
                };

                forOwn(this.formField.fieldGroups, (groupField, key) => {

                    if (!isEmpty(groupField.fields)) {

                        forOwn(groupField.fields, (field, key) => {
                            if (typeof field.value === 'undefined') {
                                form[ key ] = undefined;
                            } else {
                                form[ key ] = field.value;

                                if (field.is_translated && field.value.length == 0) {
                                    fieldValue = {};

                                    this.localeOptions.forEach(function(locale) {
                                        fieldValue[ locale.id ] = null
                                    })

                                    form[ key ] = fieldValue;
                                }
                            }
                        });
                    }
                });

                return useForm(form);
            },

            submit() {
                const self = this;

                this
                    .form
                    .transform((data) => ({
                        ...data,
                        key: self.formField.key,
                        route_name: self.routeName,
                    }))
                    .post(
                        route(self.routeSave),
                        {
                            preserveScroll: true,
                            onStart: () => {
                                self.onStartLoadingOverlay();
                            },
                            onSuccess: (page) => {
                                successAlert(page.props.flash.message);

                                self.$emit('on-success-submit');
                                self.formFieldKey += 1;
                            },
                            onError: errors => {
                                oopsAlert({
                                    text: errors?.formBuilder?.default[0] ?? null,
                                    isScrollToTop: false
                                });
                            },
                            onFinish: (visit) => {
                                self.onEndLoadingOverlay();
                            },
                        }
                    );
            },
        },
    }
</script>