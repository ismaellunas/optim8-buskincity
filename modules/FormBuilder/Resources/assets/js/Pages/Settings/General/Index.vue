<template>
    <div>
        <general-form
            v-if="isShown"
            v-model="form"
            @on-submit="onSubmit()"
        />
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import GeneralForm from './Form';
    import { usePage, useForm } from '@inertiajs/inertia-vue3';

    export default {
        name: 'General',

        components: {
            GeneralForm,
        },

        mixins: [
            MixinHasLoader,
        ],

        setup() {
            return {
                baseRouteNameSetting: usePage().props.value.baseRouteNameSetting,
                formBuilder: usePage().props.value.formBuilder,
                form: useForm([]),
            };
        },

        data() {
            return {
                isShown: false,
            };
        },

        computed: {
            baseRouteName() {
                return this.baseRouteNameSetting + '.general';
            },
        },

        mounted() {
            this.getSettings();
        },

        methods: {
            getSettings() {
                const self = this;

                self.onStartLoadingOverlay()

                axios.get(
                    route(self.baseRouteName + '.form', self.formBuilder.id)
                )
                    .then((response) => {
                        self.form = useForm(response.data);
                    })
                    .then(() => {
                        self.isShown = true;

                        self.onEndLoadingOverlay()
                    });
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
