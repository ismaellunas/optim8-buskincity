<template>
    <div class="columns">
        <div class="column is-4">
            <sdb-form-input
                label="Title"
                v-model="title"
                :message="error('title')"
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
                label="Status"
                v-model="status"
                :message="error('status')"
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
        label="Excerpt"
        v-model="excerpt"
        :message="error('excerpt')"
        placeholder="..."
        :disabled="disableInput"
        rows="2"
    />
    <sdb-form-input
        label="Meta Description"
        v-model="meta_description"
        :message="error('meta_description')"
        placeholder="meta description"
        :disabled="disableInput"
    />
    <sdb-form-input
        label="Meta Title"
        v-model="meta_title"
        :message="error('meta_title')"
        placeholder="meta title"
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
