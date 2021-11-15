<template>
    <fieldset>
        <sdb-form-input
            v-model="form.title"
            label="Title"
            required
            :message="error('title', null, errors)"
        />

        <sdb-form-select
            v-model="form.type"
            class="is-fullwidth"
            label="Type"
            required
            :message="error('type', null, errors)"
            @change="$emit('on-changed-type')"
        >
            <template
                v-for="option in typeOptions"
                :key="option.id"
            >
                <option :value="option.id">
                    {{ option.value }}
                </option>
            </template>
        </sdb-form-select>

        <sdb-form-input
            v-if="isTypeUrl"
            v-model="form.url"
            label="Url"
            placeholder="e.g https://www.example.com/"
            :message="error('url', null, errors)"
        />

        <sdb-form-select
            v-if="isTypePage"
            v-model="form.page_id"
            label="Link Page"
            class="is-fullwidth"
            :message="error('page_id', null, errors)"
        >
            <template
                v-for="option in pageOptions"
                :key="option.id"
            >
                <option :value="option.id">
                    {{ option.value }}
                    <template
                        v-for="locale, index in option.locales"
                        :key="index"
                    >
                        [{{ locale.toUpperCase() }}]
                    </template>
                </option>
            </template>
        </sdb-form-select>

        <sdb-form-select
            v-if="isTypePost"
            v-model="form.post_id"
            label="Link Post"
            class="is-fullwidth"
            :message="error('post_id', null, errors)"
        >
            <template
                v-for="option in postOptions"
                :key="option.id"
            >
                <option :value="option.id">
                    {{ option.value }} [{{ option.locale.toUpperCase() }}]
                </option>
            </template>
        </sdb-form-select>

        <sdb-form-select
            v-if="isTypeCategory"
            v-model="form.category_id"
            label="Link Category"
            class="is-fullwidth"
            :message="error('category_id', null, errors)"
        >
            <template
                v-for="option in categoryOptions"
                :key="option.id"
            >
                <option :value="option.id">
                    {{ option.value }}
                    <template
                        v-for="locale, index in option.locales"
                        :key="index"
                    >
                        [{{ locale.toUpperCase() }}]
                    </template>
                </option>
            </template>
        </sdb-form-select>
    </fieldset>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import SdbFormInput from '@/Sdb/Form/Input';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'MenuItemFields',

        components: {
            SdbFormInput,
            SdbFormSelect,
        },


        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            errors: {
                type: Object,
                required: true,
            },
            modelValue: {
                type: Object,
                required: true,
            },
            typeOptions: {
                type: Object,
                required: true,
            },
            categoryOptions: {
                type: Object,
                default: () => {}
            },
            pageOptions: {
                type: Object,
                default: () => {}
            },
            postOptions: {
                type: Object,
                default: () => {}
            },
        },

        emits: [
            'on-changed-type',
        ],

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        computed: {
            isTypeUrl() {
                return this.form.type == '1';
            },

            isTypePage() {
                return this.form.type == '2';
            },

            isTypePost() {
                return this.form.type == '3';
            },

            isTypeCategory() {
                return this.form.type == '4'
            },
        },
    };
</script>
