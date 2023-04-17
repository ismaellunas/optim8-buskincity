<template>
    <div class="box mb-6">
        <form
            action=""
            @submit.prevent="submit"
        >
            <space-form
                v-model="space"
                :parent-options="parentOptions"
                :type-options="typeOptions"
                :default-country="defaultCountry"
                :instructions="instructions"
            />
            <div class="field is-grouped is-grouped-right mt-4">
                <div class="control">
                    <biz-button-link
                        :href="routeIndex"
                        class="is-link is-light"
                    >
                        {{ i18n.cancel }}
                    </biz-button-link>
                </div>
                <div class="control">
                    <biz-button class="is-link">
                        {{ i18n.create }}
                    </biz-button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import SpaceForm from './SpaceForm.vue';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            BizButton,
            BizButtonLink,
            SpaceForm,
        },

        mixins: [
            MixinHasLoader,
        ],

        provide() {
            return {
                i18n: this.i18n,
            };
        },

        layout: AppLayout,

        props: {
            breadcrumbs: { type: Object, required: true },
            baseRouteName: { type: String, default: '' },
            defaultCountry: { type: String, required: true },
            instructions: { type: Object, required: true },
            parentOptions: { type: Object, default: () => {} },
            title: { type: String, default: "" },
            typeOptions: { type: Object, default: () => {} },
            i18n: { type: Object, default: () => ({
                cancel: 'Cancel',
                create: 'Create',
            }) },
        },

        setup(props) {
            return {
                routeIndex: route(props.baseRouteName+'.index'),
            };
        },

        data() {
            return {
                space: {
                    address: null,
                    latitude: null,
                    longitude: null,
                    name: null,
                    parent_id: this.parentOptions[0]?.id ?? null,
                    type_id: null,
                    contacts: [],
                    logo: null,
                    cover: null,
                },
            };
        },

        methods: {
            submit() {
                const self = this;
                const form = useForm(self.space);

                form.post(route(self.baseRouteName+'.store'), {
                    onStart: () => {
                        self.onStartLoadingOverlay();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: () => {
                        self.onEndLoadingOverlay();
                    }
                });
            },
        },
    };
</script>
