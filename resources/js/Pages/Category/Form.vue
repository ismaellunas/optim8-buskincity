<template>
    <div>
        <div class="columns my-0">
            <div class="column py-0">
                <p class="buttons is-pulled-right">
                    <biz-button
                        v-for="locale in localeOptions"
                        :key="locale.id"
                        :class="['is-small is-link is-rounded', locale.id == selectedLocale ? '' : 'is-light' ]"
                        @click="$emit('change-locale', locale.id)"
                    >
                        {{ locale.name }}
                    </biz-button>
                </p>
            </div>
        </div>

        <form
            method="post"
            @submit.prevent="$emit('on-submit', form)"
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
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSlug from '@/Biz/Form/Slug';
    import { isEmpty } from 'lodash';
    import { useModelWrapper, convertToSlug } from '@/Libs/utils';

    export default {
        name: 'CategoryForm',

        components: {
            BizButton,
            BizButtonLink,
            BizFormInput,
            BizFormSlug,
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
            'change-locale',
            'on-submit',
        ],

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        methods: {
            populateSlug() {
                if (isEmpty(this.form.slug) && !isEmpty(this.form.name)) {
                    this.form.slug = convertToSlug(this.form.name);
                }
            },
        },
    };
</script>
