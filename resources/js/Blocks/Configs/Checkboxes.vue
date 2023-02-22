<template>
    <div>
        <biz-label class="is-size-7">
            {{ label }}
        </biz-label>
        <div
            v-for="(option, index) in options"
            :key="index"
        >
            <biz-checkbox
                v-model:checked="computedValue"
                label-class="mr-4 is-size-7"
                :value="option.id"
            >
                &nbsp;
                <span class="is-small-7"> {{ option.value }} </span>
            </biz-checkbox>
        </div>
    </div>
</template>

<script>
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizLabel from '@/Biz/Label.vue';
    import { isEmpty } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizLabel,
            BizCheckbox,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: Array, default: () => [] },
            settings: { type: Object, default: () => {} },
        },

        emits: ['update:modelValue'],

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                rawOptions: [],
            };
        },

        computed: {
            options() {
                if (!isEmpty(this.rawOptions)) {
                    return this.rawOptions;
                } else if (!isEmpty(this.settings.options)) {
                    return this.settings.options;
                }

                return [];
            }
        },

        mounted() {
            if (! this.settings.options) {
                let url = null;

                if (this.settings.optionsRoute) {
                    url = route(this.settings.optionsRoute);
                }

                if (url) {
                    const self = this;

                    axios
                        .get(url)
                        .then(function(response) {
                            self.rawOptions = response.data;
                        })
                        .catch(function(error) {
                            self.rawOptions = [];
                        });
                }
            }
        },
    };
</script>
