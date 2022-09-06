<template>
    <div>
        <biz-form-select
            v-model="computedValue"
            :label="label"
        >
            <template
                v-for="(option, index) in options"
                :key="index"
            >
                <option :value="option.value">
                    {{ option.name }}
                </option>
            </template>
        </biz-form-select>
    </div>
</template>

<script>
    import BizFormSelect from '@/Biz/Form/Select';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Select',

        components: {
            BizFormSelect,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: [String, null], required: true },
            settings: { type: Object, default: () => {} },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                options: this.settings.options,
            };
        },

        mounted() {
            if (!this.options) {
                this.getOptions();
            }
        },

        methods: {
            getOptions() {
                const self = this;
                const optionsRoute = self.settings.optionsRoute;

                if (optionsRoute) {
                    axios.get(route(optionsRoute))
                        .then((results) => {
                            self.options = results.data;

                            return;
                        })
                }

                self.options = [];
            },
        },
    }
</script>