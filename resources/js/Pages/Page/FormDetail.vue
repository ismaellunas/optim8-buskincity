<template>
    <div>
        <div class="columns">
            <div class="column is-4">
                <biz-form-input
                    v-model="computedTitle"
                    name="title"
                    :label="i18n.title"
                    placeholder="e.g A Good News"
                    required
                    :disabled="disableInput"
                    :message="error(selectedLocale+'.title')"
                    @on-blur="populateSlug"
                    @on-keypress="keyPressTitle"
                />
            </div>
            <div class="column is-4">
                <biz-form-slug
                    v-model="computedSlug"
                    name="slug"
                    :label="i18n.slug"
                    :disabled="disableInput"
                    :message="error(selectedLocale+'.slug')"
                />
            </div>
            <div class="column is-4">
                <biz-form-select
                    v-model="computedStatus"
                    class="is-fullwidth"
                    name="status"
                    :label="i18n.status"
                    :disabled="disableInput"
                    :message="error(selectedLocale+'.status')"
                >
                    <option
                        v-for="option in statusOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.value }}
                    </option>
                </biz-form-select>
            </div>
        </div>
        <biz-form-textarea
            v-model="computedExcerpt"
            :label="i18n.excerpt"
            placeholder="..."
            rows="2"
            :disabled="disableInput"
            :message="error(selectedLocale+'.excerpt')"
        />
        <biz-form-input
            v-model="computedMetaTitle"
            :label="i18n.meta_title"
            :placeholder="i18n.meta_title"
            :disabled="disableInput"
            :maxlength="maxLength.meta_title"
            :message="error(selectedLocale+'.meta_title')"
        />
        <biz-form-textarea
            v-model="computedMetaDescription"
            :label="i18n.meta_description"
            :placeholder="i18n.meta_description"
            rows="2"
            :disabled="disableInput"
            :maxlength="maxLength.meta_description"
            :message="error(selectedLocale+'.meta_description')"
        />
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormSlug from '@/Biz/Form/Slug.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import { useModelWrapper, convertToSlug } from '@/Libs/utils';
    import { isEmpty } from 'lodash';
    import { usePage } from '@inertiajs/vue3';

    export default {
        components: {
            BizFormInput,
            BizFormSelect,
            BizFormSlug,
            BizFormTextarea,
        },

        mixins: [MixinHasPageErrors],

        inject: {
            i18n: {
                default: () => ({
                    title : 'Title',
                    slug : 'Slug',
                    status : 'Status',
                    excerpt : 'Excerpt',
                    meta_title : 'Meta title',
                    meta_description : 'Meta description',
                })
            },
        },

        props: {
            disableInput: { type: Boolean, default: false },
            errors: { type: Object, default:() => {} },
            excerpt: { type: String, default: null },
            metaDescription: { type: String, default: null },
            metaTitle: { type: String, default: null },
            selectedLocale: { type: String, required: true },
            slug: { type: String, default: null },
            status: { type: Number, default: 0 },
            statusOptions: { type: Array, default: () => [] },
            title: { type: String, default: null },
        },

        emits: [
            'update:modelValue',
            'update:title',
            'update:slug',
            'update:excerpt',
            'update:metaDescription',
            'update:metaTitle',
            'update:status',
        ],

        setup(props, { emit }) {
            return {
                computedExcerpt: useModelWrapper(props, emit, 'excerpt'),
                computedMetaDescription: useModelWrapper(props, emit, 'metaDescription'),
                computedMetaTitle: useModelWrapper(props, emit, 'metaTitle'),
                computedSlug: useModelWrapper(props, emit, 'slug'),
                computedStatus: useModelWrapper(props, emit, 'status'),
                computedTitle: useModelWrapper(props, emit, 'title'),
            }
        },

        data() {
            return {
                maxLength: usePage().props.maxLength,
            };
        },

        methods: {
            keyPressTitle(event) {
                if (event.keyCode == 13) {
                    this.populateSlug(event);
                }
                return true;
            },

            populateSlug(event) {
                if (isEmpty(this.computedSlug) && !isEmpty(this.computedTitle)) {
                    this.computedSlug = convertToSlug(this.computedTitle);
                }
            },
        }
    };
</script>
