<template>
    <div class="columns">
        <div class="column is-4">
            <sdb-form-input
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
            <sdb-form-slug
                v-model="slug"
                label="Slug"
                :message="error(selectedLocale+'.slug')"
                :disabled="disableInput"
            />
        </div>
        <div class="column is-4">
            <sdb-form-select
                v-model="status"
                label="Status"
                :message="error(selectedLocale+'.status')"
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
        label="Excerpt"
        :message="error(selectedLocale+'.excerpt')"
        placeholder="..."
        :disabled="disableInput"
        rows="2"
    />
    <sdb-form-input
        v-model="meta_title"
        label="Meta Title"
        :message="error(selectedLocale+'.meta_title')"
        placeholder="meta title"
        :disabled="disableInput"
    />
    <sdb-form-input
        v-model="meta_description"
        label="Meta Description"
        :message="error(selectedLocale+'.meta_description')"
        placeholder="meta description"
        :disabled="disableInput"
    />
</template>

<script>
    import HasPageErrors from '@/Mixins/HasPageErrors';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import SdbFormSlug from '@/Sdb/Form/Slug';
    import SdbFormTextarea from '@/Sdb/Form/Textarea';
    import { useModelWrapper, convertToSlug } from '@/Libs/utils';
    import { isEmpty } from 'lodash';

    export default {
        mixins: [HasPageErrors],
        components: {
            SdbFormInput,
            SdbFormSelect,
            SdbFormSlug,
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
