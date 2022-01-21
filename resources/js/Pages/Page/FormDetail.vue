<template>
    <div class="columns">
        <div class="column is-4">
            <biz-form-input
                v-model="title"
                label="Title"
                :message="error(selectedLocale+'.title')"
                placeholder="e.g A Good News"
                :disabled="disableInput"
                required
                @on-blur="populateSlug"
                @on-keypress="keyPressTitle"
            />
        </div>
        <div class="column is-4">
            <biz-form-slug
                v-model="slug"
                label="Slug"
                :message="error(selectedLocale+'.slug')"
                :disabled="disableInput"
            />
        </div>
        <div class="column is-4">
            <biz-form-select
                v-model="status"
                label="Status"
                :message="error(selectedLocale+'.status')"
                :disabled="disableInput"
                class="is-fullwidth"
            >
                <option v-for="option in statusOptions" :value="option.id">
                    {{ option.value }}
                </option>
            </biz-form-select>
        </div>
    </div>
    <biz-form-textarea
        v-model="excerpt"
        label="Excerpt"
        :message="error(selectedLocale+'.excerpt')"
        placeholder="..."
        :disabled="disableInput"
        rows="2"
    />
    <biz-form-input
        v-model="meta_title"
        label="Meta Title"
        :message="error(selectedLocale+'.meta_title')"
        placeholder="meta title"
        :disabled="disableInput"
    />
    <biz-form-input
        v-model="meta_description"
        label="Meta Description"
        :message="error(selectedLocale+'.meta_description')"
        placeholder="meta description"
        :disabled="disableInput"
    />
</template>

<script>
    import HasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizFormSlug from '@/Biz/Form/Slug';
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import { useModelWrapper, convertToSlug } from '@/Libs/utils';
    import { isEmpty } from 'lodash';

    export default {
        mixins: [HasPageErrors],
        components: {
            BizFormInput,
            BizFormSelect,
            BizFormSlug,
            BizFormTextarea,
        },
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
        setup(props, { emit }) {
            return {
                excerpt: useModelWrapper(props, emit, 'excerpt'),
                meta_description: useModelWrapper(props, emit, 'meta_description'),
                meta_title: useModelWrapper(props, emit, 'meta_title'),
                slug: useModelWrapper(props, emit, 'slug'),
                status: useModelWrapper(props, emit, 'status'),
                title: useModelWrapper(props, emit, 'title'),
            }
        },
        methods: {
            keyPressTitle(event) {
                if (event.keyCode == 13) {
                    this.populateSlug(event);
                }
                return true;
            },
            populateSlug(event) {
                if (isEmpty(this.slug) && !isEmpty(this.title)) {
                    this.slug = convertToSlug(this.title);
                }
            },
        }
    }
</script>
