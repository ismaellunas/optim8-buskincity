<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <sdb-error-notifications
            :errors="$page.props.errors"
        />

        <div class="box mb-6">
            <form
                method="post"
                @submit.prevent="onSubmit"
            >
                <div class="field is-grouped is-grouped-right">
                    <div class="control">
                        <sdb-button
                            class="is-warning"
                            type="button"
                            @click="resetColors"
                        >
                            Reset All
                        </sdb-button>
                    </div>

                    <div class="control">
                        <sdb-button class="is-link">
                            Update
                        </sdb-button>
                    </div>
                </div>

                <fieldset :disabled="isProcessing">
                    <div
                        v-for="color in sortedColors"
                        :key="color.key"
                        class="columns"
                    >
                        <div class="column is-three-quarters">
                            <h3>{{ color.display_name }}</h3>
                        </div>
                        <div class="column">
                            <sdb-input-color
                                v-model="form[color.key]"
                            />
                            <p v-if="error(color.key)">
                                {{ error(color.key) }}
                            </p>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbInputColor from '@/Sdb/InputColor';
    import { forEach, has, isEmpty, mapValues, sortBy } from 'lodash';
    import { confirm as confirmAlert, success as successAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'ThemeOptionColorEdit',

        components: {
            AppLayout,
            SdbButton,
            SdbErrorNotifications,
            SdbInputColor,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            baseRouteName: String,
            can: Object,
            colors: Object,
            defaultColors: Object,
            errors: Object,
            title: String,
        },

        setup(props) {
            const colors = mapValues(props.colors, (color) => {
                return color.value;
            });

            const form = mapValues(props.colors, (color, key) => {
                const colorValue = props.colors[key].value;
                if (isEmpty(colorValue) && has(props.defaultColors, key)) {
                    colorValue = props.defaultColors[key];
                }
                return colorValue;
            });

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                isProcessing: false,
                loader: null,
            };
        },

        computed: {
            sortedColors() {
                return sortBy(this.colors, ['order']);
            },
        },

        methods: {
            onSubmit() {
                const self = this;
                this.form.post(route(this.baseRouteName+'.update'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.form.isDirty = false;
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;
                    }
                });
            },
            resetColors() {
                const self = this;
                confirmAlert('Are you sure you want to reset?')
                    .then((result) => {
                        if (result.isConfirmed) {
                            self.form.reset();
                        }
                    });
            }
        },
    };
</script>
