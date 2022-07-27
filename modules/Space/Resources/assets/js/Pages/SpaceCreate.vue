<template>
    <app-layout :title="title">
        <template #header>
            {{ title }}
        </template>

        <div class="box mb-6">
            <form
                action=""
                @submit.prevent="submit"
            >
                <space-form
                    v-model="space"
                    :parent-options="parentOptions"
                    :type-options="typeOptions"
                    :country-options="countryOptions"
                    :default-country="defaultCountry"
                />
                <div class="field is-grouped is-grouped-right mt-4">
                    <div class="control">
                        <biz-button-link
                            :href="routeIndex"
                            class="is-link is-light"
                        >
                            Cancel
                        </biz-button-link>
                    </div>
                    <div class="control">
                        <biz-button class="is-link">
                            Create
                        </biz-button>
                    </div>
                </div>
            </form>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import SpaceForm from './SpaceForm';
    import { ref } from "vue";
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonLink,
            SpaceForm,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            baseRouteName: { type: String, default: '' },
            parentOptions: { type: Object, default: () => {} },
            typeOptions: { type: Object, default: () => {} },
            title: { type: String, default: "" },
            countryOptions: { type: Array, default: () => [] },
            defaultCountry: { type: String, required: true },
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
                    parent_id: this.parentOptions[0].id ?? null,
                    type_id: null,
                    contacts: [],
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
