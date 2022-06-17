<template>
    <div>
        <div class="columns">
            <div class="column is-4">
                <biz-form-input
                    v-model="computedTitle"
                    label="Title"
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
                    label="Slug"
                    :disabled="disableInput"
                    :message="error(selectedLocale+'.slug')"
                />
            </div>
            <div class="column is-4">
                <biz-form-select
                    v-model="computedStatus"
                    class="is-fullwidth"
                    label="Status"
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
            label="Excerpt"
            placeholder="..."
            rows="2"
            :disabled="disableInput"
            :message="error(selectedLocale+'.excerpt')"
        />
        <biz-form-input
            v-model="computedMeta_title"
            label="Meta Title"
            placeholder="Meta title"
            :disabled="disableInput"
            :maxlength="maxLength.meta_title"
            :message="error(selectedLocale+'.meta_title')"
        />
        <biz-form-textarea
            v-model="computedMeta_description"
            label="Meta Description"
            placeholder="Meta description"
            rows="2"
            :disabled="disableInput"
            :maxlength="maxLength.meta_description"
            :message="error(selectedLocale+'.meta_description')"
        />
    </div>
</template>

<script>
    import HasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizFormSlug from '@/Biz/Form/Slug';
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import { useModelWrapper, convertToSlug } from '@/Libs/utils';
    import { isEmpty } from 'lodash';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizFormInput,
            BizFormSelect,
            BizFormSlug,
            BizFormTextarea,
        },

        mixins: [HasPageErrors],

        props: [
            'title',
            'slug',
            'excerpt',
            'meta_description',
            'meta_title',
            'status',
            'errors',
            'disableInput',
            'statusOptions',
            'selectedLocale',
        ],

        emits: [
            'update:modelValue',
            'update:title',
            'update:slug',
            'update:excerpt',
            'update:meta_description',
            'update:meta_title',
            'update:status',
        ],

        setup(props, { emit }) {
            return {
                computedExcerpt: useModelWrapper(props, emit, 'excerpt'),
                computedMeta_description: useModelWrapper(props, emit, 'meta_description'),
                computedMeta_title: useModelWrapper(props, emit, 'meta_title'),
                computedSlug: useModelWrapper(props, emit, 'slug'),
                computedStatus: useModelWrapper(props, emit, 'status'),
                computedTitle: useModelWrapper(props, emit, 'title'),
            }
        },

        data() {
            return {
                maxLength: usePage().props.value.maxLength,
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
