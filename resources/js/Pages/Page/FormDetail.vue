<template>
    <sdb-form-input
        label="Title"
        v-model="title"
        :message="error('title')"
        placeholder="e.g A Good News"
        :disabled="disableInput"
    />
    <sdb-form-input
        label="Slug"
        v-model="slug"
        :message="error('slug')"
        placeholder="e.g. a-good-news"
        :disabled="disableInput"
    />
    <sdb-form-textarea
        label="Excerpt"
        v-model="excerpt"
        :message="error('excerpt')"
        placeholder="..."
        :disabled="disableInput"
    />
    <sdb-form-textarea
        label="Meta Description"
        v-model="meta_description"
        :message="error('meta_description')"
        placeholder="..."
        :disabled="disableInput"
    />
    <sdb-form-textarea
        label="Meta Keywords"
        v-model="meta_keywords"
        :message="error('meta_keywords')"
        placeholder="Keyword1, Keyword2"
        :disabled="disableInput"
    />
    <sdb-form-select
        label="Status"
        v-model="status"
        :message="error('status')"
        :disabled="disableInput"
    >
        <option v-for="option in statusOptions" :value="option.id">
            {{ option.value }}
        </option>
    </sdb-form-select>
</template>

<script>
    import HasPageErrors from '@/Mixins/HasPageErrors';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import SdbFormTextarea from '@/Sdb/Form/Textarea';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        mixins: [HasPageErrors],
        components: {
            SdbFormInput,
            SdbFormSelect,
            SdbFormTextarea,
        },
        props: [
            'title',
            'slug',
            'excerpt',
            'meta_description',
            'meta_keywords',
            'status',
            'errors',
            'disableInput',
            'statusOptions',
        ],
        setup(props, { emit }) {
            return {
                excerpt: useModelWrapper(props, emit, 'excerpt'),
                meta_description: useModelWrapper(props, emit, 'meta_description'),
                meta_keywords: useModelWrapper(props, emit, 'meta_keywords'),
                slug: useModelWrapper(props, emit, 'slug'),
                status: useModelWrapper(props, emit, 'status'),
                title: useModelWrapper(props, emit, 'title'),
            }
        },
    }
</script>
