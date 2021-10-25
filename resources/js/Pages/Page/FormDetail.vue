<template>
    <div class="columns">
        <div class="column is-4">
            <sdb-form-input
                v-model="title"
                :message="error('title')"
                label="Title"
                placeholder="e.g A Good News"
                :disabled="disableInput"
                required
            />
        </div>
        <div class="column is-4">
            <sdb-form-input
                label="Slug"
                v-model="slug"
                :message="error('slug')"
                placeholder="e.g. a-good-news"
                :disabled="disableInput"
            />
        </div>
        <div class="column is-4">
            <sdb-form-select
                v-model="status"
                :message="error('status')"
                label="Status"
                :disabled="disableInput"
                class="is-fullwidth"
            >
                <option v-for="option in statusOptions" :value="option.id">
                    {{ option.value }}
                </option>
            </sdb-form-select>
        </div>
    </div>
    <sdb-form-textarea
        v-model="excerpt"
        :message="error('excerpt')"
        label="Excerpt"
        placeholder="..."
        :disabled="disableInput"
        rows="2"
    />
    <sdb-form-input
        v-model="meta_title"
        :message="error('meta_title')"
        label="Meta Title"
        placeholder="meta title"
        :disabled="disableInput"
    />
    <sdb-form-input
        v-model="meta_description"
        :message="error('meta_description')"
        label="Meta Description"
        placeholder="meta description"
        :disabled="disableInput"
    />
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
    }
</script>
