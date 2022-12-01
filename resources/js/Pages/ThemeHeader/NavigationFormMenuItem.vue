<template>
    <biz-modal-card
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
                <biz-form-input
                    v-model="form.title"
                    label="Title"
                    required
                    :message="error('title', null, errors)"
                />

                <biz-form-select
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
                </biz-form-select>

                <biz-form-input
                    v-if="isTypeUrl"
                    v-model="form.url"
                    label="Url"
                    placeholder="e.g https://www.example.com/"
                    :message="error('url', null, errors)"
                />

                <biz-form-select
                    v-else
                    v-model="form.menu_itemable_id"
                    label="Menu"
                    class="is-fullwidth"
                    :message="error('menu_itemable_id', null, errors)"
                    :required="true"
                >
                    <option
                        v-for="option in menuOptions[form.type]"
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
                </biz-form-select>

                <biz-checkbox
                    v-model:checked="form.is_blank"
                    :value="true"
                >
                    Open link in a new tab
                </biz-checkbox>
            </fieldset>
        </form>

        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button @click="$emit('close')">
                            Cancel
                        </biz-button>
                        <biz-button
                            class="is-primary ml-1"
                            type="button"
                            @click="onSubmit()"
                        >
                            {{ isCreate ? 'Create' : 'Update' }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizModalCard from '@/Biz/ModalCard';
    import { cloneDeep } from 'lodash';
    import { isBlank } from '@/Libs/utils';
    import { reactive } from 'vue';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'NavigationFormMenu',

        components: {
            BizButton,
            BizCheckbox,
            BizFormInput,
            BizFormSelect,
            BizModalCard,
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
                    type: 'url',
                    url: null,
                    order: null,
                    is_blank: false,
                    parent_id: null,
                    menu_id: props.menu.id,
                    menu_itemable_id: null,
                    children: [],
                });
            }

            return {
                defaultLocale: usePage().props.value.defaultLanguage,
                firstFields: cloneDeep(fields),
                form: fields,
                menuOptions: usePage().props.value.menuOptions,
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
                return this.form.type == 'url';
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
                this.form.menu_itemable_id = null;
            }
        },
    };
</script>
