<template>
    <div>
        <general-form
            v-if="isFormShown"
            v-model="form"
            @on-submit="onSubmit()"
        />
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import GeneralForm from './Form.vue';
    import { cloneDeep } from 'lodash';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { usePage, useForm } from '@inertiajs/vue3';

    export default {
        name: 'GeneralSetting',

        components: {
            GeneralForm,
        },

        mixins: [
            MixinHasLoader,
        ],

        setup() {
            return {
                baseRouteNameSetting: usePage().props.baseRouteNameSetting,
                formBuilder: usePage().props.formBuilder,
            };
        },

        data() {
            return {
                form: null,
            };
        },

        computed: {
            baseRouteName() {
                return this.baseRouteNameSetting + '.general';
            },

            isFormShown() {
                return !!this.form;
            },
        },

        mounted() {
            this.createForm();
        },

        methods: {
            createForm() {
                this.form = useForm(
                    cloneDeep(this.formBuilder.setting)
                );
            },

            onSubmit() {
                this.form.put(
                    route(this.baseRouteName + '.update', this.formBuilder.id),
                    {
                        onStart: () => this.onStartLoadingOverlay(),
                        onSuccess: (page) => successAlert(page.props.flash.message),
                        onError: () => oopsAlert(),
                        onFinish: () => this.onEndLoadingOverlay(),
                    }
                )
            },
        }
    };
</script>
