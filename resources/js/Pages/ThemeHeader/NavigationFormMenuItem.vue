<template>
    <sdb-modal-card>
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ menuItem.id ? 'Edit' : 'Add' }} Menu Item
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
                    :message="error('title')"
                />
                <sdb-form-select
                    v-model="form.type"
                    label="Type"
                    class="is-fullwidth"
                    required
                    :message="error('type')"
                >
                    <template
                        v-for="(type, index) in types"
                        :key="index"
                    >
                        <option :value="type">
                            {{ type }}
                        </option>
                    </template>
                </sdb-form-select>
                <sdb-form-input
                    v-if="isTypeUrl"
                    v-model="form.url"
                    label="Url"
                    placeholder="e.g https:://example.com/"
                    :message="error('url')"
                />
                <sdb-form-select
                    v-if="isTypePage"
                    v-model="form.page_id"
                    label="Link Page"
                    class="is-fullwidth"
                    :message="error('page_id')"
                >
                    <template
                        v-for="option in pageOptions"
                        :key="option.id"
                    >
                        <option :value="option.id">
                            {{ option.value }}
                        </option>
                    </template>
                </sdb-form-select>
                <sdb-form-select
                    v-if="isTypePost"
                    v-model="form.post_id"
                    label="Link Post"
                    class="is-fullwidth"
                    :message="error('post_id')"
                >
                    <template
                        v-for="option in postOptions"
                        :key="option.id"
                    >
                        <option :value="option.id">
                            {{ option.value }}
                        </option>
                    </template>
                </sdb-form-select>
                <sdb-form-select
                    v-if="isTypeCategory"
                    v-model="form.category_id"
                    label="Link Category"
                    class="is-fullwidth"
                    :message="error('category_id')"
                >
                    <template
                        v-for="option in categoryOptions"
                        :key="option.id"
                    >
                        <option :value="option.id">
                            {{ option.value }}
                        </option>
                    </template>
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
                            {{ menuItem.id ? 'Update' : 'Create' }}
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
    import { isBlank } from '@/Libs/utils';
    import { pull, sortBy, merge } from 'lodash';
    import { reactive } from "vue";
    import { success as successAlert, confirmDelete } from '@/Libs/alert';
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
            menu: {
                type: Object,
                required: true,
            },
            menuItem: {
                type: Object,
                default: {},
            },
            selectedLocale: {
                type: String,
                default: "en",
            },
        },

        emits: [
            'close',
            'syncMenuItems',
        ],

        setup(props) {
            let fields = {};

            if (!isBlank(props.menuItem)) {
                fields = props.menuItem;
            } else {
                fields = {
                    id: null,
                    locale: props.selectedLocale,
                    title: null,
                    type: 'Url',
                    url: null,
                    page_id: null,
                    post_id: null,
                    category_id: null,
                    menu_id: props.menu.id,
                };
            }

            return {
                categoryOptions: sortBy(usePage().props.value.categoryOptions, [(option) => option.value]),
                defaultLocale: usePage().props.value.defaultLanguage,
                form: reactive(fields),
                pageOptions: sortBy(usePage().props.value.pageOptions, [(option) => option.value]),
                postOptions: sortBy(usePage().props.value.postOptions, [(option) => option.value]),
                types: usePage().props.value.types,
            };
        },

        data() {
            return {
                loader: null,
            };
        },

        computed: {
            isTypeUrl() {
                return this.form.type == 'Url';
            },

            isTypePage() {
                return this.form.type == 'Page';
            },

            isTypePost() {
                return this.form.type == 'Post';
            },

            isTypeCategory() {
                return this.form.type == 'Category'
            },
        },

        methods: {
            onSubmit() {
                const self = this;
                const form = this.form;

                if (form.id === null) {
                    this.$inertia.post(route(this.baseRouteName+'.store'), form, {
                        preserveState: true,
                        onStart: () => {
                            self.loader = self.$loading.show();
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                        },
                        onFinish: () => {
                            self.loader.hide();
                            this.$emit('close');
                            this.$emit('syncMenuItems');
                        }
                    });
                } else {
                    this.$inertia.post(route(this.baseRouteName+'.update', form.id), form, {
                        preserveState: true,
                        onStart: () => {
                            self.loader = self.$loading.show();
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                        },
                        onFinish: () => {
                            self.loader.hide();
                            this.$emit('close');
                            this.$emit('syncMenuItems');
                        }
                    });
                }
            },
        },
    }
</script>
