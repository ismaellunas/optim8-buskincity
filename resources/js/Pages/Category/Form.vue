<template>
    <div>
        <div class="columns my-0">
            <div class="column py-0">
                <biz-language-tab
                    class="is-pulled-right"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @on-change-locale="onChangeLocale"
                />
            </div>
        </div>

        <form
            method="post"
            @submit.prevent="onSubmit()"
        >
            <biz-form-input
                v-model="form.name"
                label="Name"
                :message="error(selectedLocale+'.name')"
                placeholder="e.g Good News"
                :disabled="isInputDisabled"
                required
                @on-blur="populateSlug()"
            />

            <biz-form-slug
                v-model="form.slug"
                label="Slug"
                placeholder="e.g good-news"
                :message="error(selectedLocale+'.slug')"
                :disabled="isInputDisabled"
            />

            <biz-form-input
                v-model="form.meta_title"
                label="Meta title"
                placeholder="Meta title"
                :disabled="isInputDisabled"
                :maxlength="maxLength.meta_title"
                :message="error(selectedLocale+'.meta_title')"
            />

            <biz-form-textarea
                v-model="form.meta_description"
                label="Meta description"
                placeholder="Meta description"
                rows="2"
                :disabled="isInputDisabled"
                :maxlength="maxLength.meta_description"
                :message="error(selectedLocale+'.meta_description')"
            />

            <div class="field is-grouped is-grouped-right mt-5">
                <div class="control">
                    <biz-button-link
                        :href="route(baseRoute+'.index')"
                        class="is-link is-light"
                    >
                        Cancel
                    </biz-button-link>
                </div>
                <div class="control">
                    <biz-button class="is-link">
                        <template v-if="isNew">
                            Create
                        </template>
                        <template v-else>
                            Update
                        </template>
                    </biz-button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import HasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizLanguageTab from '@/Biz/LanguageTab';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSlug from '@/Biz/Form/Slug';
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import { isEmpty } from 'lodash';
    import { useModelWrapper, convertToSlug } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'CategoryForm',

        components: {
            BizButton,
            BizButtonLink,
            BizLanguageTab,
            BizFormInput,
            BizFormSlug,
            BizFormTextarea,
        },

        mixins: [
            HasPageErrors
        ],

        props: {
            baseRoute: { type: String, required: true },
            defaultLocale: { type: String, required: true },
            errors: { type: Object, default:() => {} },
            isInputDisabled: {type: Boolean, default: false},
            isNew: { type: Boolean, default: false },
            localeOptions: { type: Array, default:() => [] },
            modelValue: { type: Object, required: true },
            selectedLocale: { type: String, required: true },
        },

        emits: [
            'on-change-locale',
            'on-submit',
        ],

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                maxLength: usePage().props.value.maxLength,
            };
        },

        methods: {
            onSubmit() {
                this.populateSlug();

                this.$emit('on-submit', this.form);
            },

            populateSlug() {
                if (isEmpty(this.form.slug) && !isEmpty(this.form.name)) {
                    this.form.slug = convertToSlug(this.form.name);
                }
            },

            onChangeLocale(localeId) {
                this.$emit('on-change-locale', localeId);
            }
        },
    };
</script>
