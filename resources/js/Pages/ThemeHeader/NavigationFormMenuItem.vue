<template>
    <sdb-modal-card
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ isCreate ? 'Add' : 'Edit' }} Menu Item
            </p>
            <button
                class="delete"
                aria-label="close"
                @click="$emit('close')"
            />
        </template>

        <form @submit.prevent="onSubmit">
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
                    @change="onChangedType"
                >
                    <option
                        v-for="option in typeOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.value }}
                    </option>
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
                    <option
                        v-for="option in pageOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.value }}
                        <span
                            v-for="locale, index in option.locales"
                            :key="index"
                        >
                            [{{ locale.toUpperCase() }}]
                        </span>
                    </option>
                </sdb-form-select>

                <sdb-form-select
                    v-if="isTypePost"
                    v-model="form.post_id"
                    label="Link Post"
                    class="is-fullwidth"
                    :message="error('post_id', null, errors)"
                >
                    <option
                        v-for="option in postOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.value }} [{{ option.locale.toUpperCase() }}]
                    </option>
                </sdb-form-select>

                <sdb-form-select
                    v-if="isTypeCategory"
                    v-model="form.category_id"
                    label="Link Category"
                    class="is-fullwidth"
                    :message="error('category_id', null, errors)"
                >
                    <option
                        v-for="option in categoryOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.value }}
                        <span
                            v-for="locale, index in option.locales"
                            :key="index"
                        >
                            [{{ locale.toUpperCase() }}]
                        </span>
                    </option>
                </sdb-form-select>
            </fieldset>
        </form>

        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div class="column">
                    <div class="is-pulled-right">
                        <sdb-button @click="$emit('close')">
                            Cancel
                        </sdb-button>
                        <sdb-button
                            class="is-primary ml-1"
                            type="button"
                            @click="onSubmit()"
                        >
                            {{ isCreate ? 'Create' : 'Update' }}
                        </sdb-button>
                    </div>
                </div>
            </div>
        </template>
    </sdb-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import SdbModalCard from '@/Sdb/ModalCard';
    import { cloneDeep, sortBy } from 'lodash';
    import { isBlank } from '@/Libs/utils';
    import { reactive } from 'vue';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'NavigationFormMenu',

        components: {
            SdbButton,
            SdbFormInput,
            SdbFormSelect,
            SdbModalCard,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            baseRouteName: {
                type: String,
                required: true,
            },
            errors: {
                type: Object,
                default: () => {},
            },
            menu: {
                type: Object,
                required: true,
            },
            menuItem: {
                type: Object,
                default: () => {},
            },
            selectedLocale: {
                type: String,
                default: "en",
            },
        },

        emits: [
            'add-menu-item',
            'close',
            'update-menu-item',
        ],

        setup(props) {
            let fields = {};

            if (!isBlank(props.menuItem)) {
                fields = reactive(cloneDeep(props.menuItem));
            } else {
                fields = reactive({
                    id: null,
                    title: null,
                    type: 1,
                    url: null,
                    order: null,
                    parent_id: null,
                    menu_id: props.menu.id,
                    page_id: null,
                    post_id: null,
                    category_id: null,
                    children: [],
                });
            }

            return {
                categoryOptions: sortBy(usePage().props.value.categoryOptions, [(option) => option.value]),
                defaultLocale: usePage().props.value.defaultLanguage,
                form: fields,
                firstFields: cloneDeep(fields),
                pageOptions: sortBy(usePage().props.value.pageOptions, [(option) => option.value]),
                postOptions: sortBy(usePage().props.value.postOptions, [(option) => option.value]),
                typeOptions: usePage().props.value.typeOptions,
            };
        },

        data() {
            return {
                loader: null,
            };
        },

        computed: {
            isCreate() {
                return isBlank(this.menuItem);
            },

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

        methods: {
            onSubmit() {
                const self = this;
                const form = this.form;

                if (isBlank(this.menuItem)) {
                    this.$emit('add-menu-item', form);
                } else {
                    this.$emit('update-menu-item', form);
                }
            },

            onChangedType() {
                this.form.url = null;
                this.form.page_id = null;
                this.form.post_id = null;
                this.form.category_id = null;
            }
        },
    };
</script>
