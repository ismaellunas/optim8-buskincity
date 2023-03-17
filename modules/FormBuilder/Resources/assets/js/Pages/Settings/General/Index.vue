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
    import { usePage, useForm } from '@inertiajs/vue3';
    import { cloneDeep } from 'lodash';

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
                const self = this;

                self.form.put(
                    route(self.baseRouteName + '.update', self.formBuilder.id),
                    {
                        onStart: () => self.onStartLoadingOverlay(),
                        onFinish: () => self.onEndLoadingOverlay(),
                    }
                )
            },
        }
    };
</script>
