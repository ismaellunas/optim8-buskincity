<template>
    <div>
        <div class="columns">
            <div class="column">
                <h2>
                    <b>Layout</b>
                </h2>
            </div>

            <div class="column">
                <biz-form-select
                    v-model="form.layout"
                    :is-fullwidth="true"
                    :message="error(selectedLocale+'.settings.layout')"
                >
                    <option :value="null">
                        (Default)
                    </option>
                    <option
                        v-for="option in templateOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.value }}
                    </option>
                </biz-form-select>
            </div>
        </div>

        <div class="columns">
            <div class="column">
                <h2>
                    <b>Main Background Color</b>
                </h2>
            </div>

            <div class="column">
                <biz-form-select
                    v-model="form.main_background_color"
                    :is-fullwidth="true"
                    :message="error(selectedLocale+'.settings.main_background_color')"
                >
                    <option :value="null">
                        (Default)
                    </option>
                    <option
                        v-for="(option, index) in backgroundColorOptions"
                        :key="index"
                        :value="option.value"
                    >
                        {{ option.name }}
                    </option>
                </biz-form-select>
            </div>
        </div>

        <div class="columns">
            <div class="column">
                <h2>
                    <b>Page Height</b>
                </h2>
            </div>

            <div class="column">
                <biz-form-number-addons
                    v-model="form.height"
                    :message="error(selectedLocale+'.settings.height')"
                >
                    <template #afterInput>
                        <p class="control">
                            <a class="button is-static">
                                vh
                            </a>
                        </p>
                    </template>
                </biz-form-number-addons>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormNumberAddons from '@/Biz/Form/NumberAddons.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { backgroundColors } from '@/ComponentStructures/style-options';

    export default {
        name: 'PageFormSetting',

        components: {
            BizFormNumberAddons,
            BizFormSelect,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: ['settingOptions'],

        props: {
            errors: { type: Object, default:() => {} },
            modelValue: { type: Object, required: true },
            selectedLocale: { type: String, required: true },
        },

        setup(props, {emit}) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        computed: {
            templateOptions() {
                return this.settingOptions.templates;
            },

            backgroundColorOptions() {
                return backgroundColors;
            },
        },
    }
</script>