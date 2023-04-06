<template>
    <biz-modal-card
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ i18n.duplicate_menu }}
            </p>
            <button
                class="delete"
                aria-label="close"
                @click="$emit('close')"
            />
        </template>

        <form @submit.prevent="onSubmit">
            <fieldset>
                <biz-form-select
                    v-model="form.locale"
                    class="is-fullwidth"
                    :label="i18n.to"
                    required
                    :message="error('locale', null, errors)"
                >
                    <option
                        v-for="locale in localeOptions"
                        :key="locale.id"
                        :value="locale.id"
                    >
                        {{ locale.name }}
                    </option>
                </biz-form-select>

                <biz-form-input
                    v-model="form.title"
                    :label="i18n.title"
                    required
                    :message="error('title', null, errors)"
                />

                <biz-form-select
                    v-model="form.type"
                    class="is-fullwidth"
                    :label="i18n.type"
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
                    :label="i18n.url"
                    placeholder="e.g https://www.example.com/"
                    :message="error('url', null, errors)"
                />

                <biz-form-select
                    v-else
                    v-model="form.menu_itemable_id"
                    :label="i18n.menu"
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
                    <span class="ml-2">
                        {{ i18n.open_link }}
                    </span>
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
                            {{ i18n.cancel }}
                        </biz-button>
                        <biz-button
                            class="is-primary ml-1"
                            type="button"
                            @click="onSubmit()"
                        >
                            {{ i18n.duplicate }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import { cloneDeep } from 'lodash';
    import { reactive } from 'vue';
    import { usePage } from '@inertiajs/vue3';

    export default {
        name: 'NavigationFormDuplicate',

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

        inject: {
            i18n: { default: () => ({
                duplicate_menu: 'Duplicate Menu',
                to: 'To',
                title : 'Title',
                type : 'Type',
                url : 'Url',
                menu : 'Menu',
                open_link : 'Open link in a new tab',
                cancel : 'Cancel',
                duplicate: 'Duplicate',
            }) },
        },

        props: {
            errors: {
                type: Object,
                default: () => {},
            },
            localeOptions: {
                type: Array,
                default:() => {},
            },
            menuItem: {
                type: Object,
                default: () => {},
            },
        },

        emits: [
            'close',
            'duplicate-menu-item',
        ],

        setup(props) {
            return {
                form: reactive(cloneDeep(props.menuItem)),
                menuOptions: usePage().props.menuOptions,
                typeOptions: usePage().props.typeOptions,
            };
        },

        data() {
            return {
                loader: null,
            };
        },

        computed: {
            isTypeUrl() {
                return this.form.type == 'url';
            },
        },

        methods: {
            onSubmit() {
                const form = this.form;

                this.$emit('duplicate-menu-item', form);
            },

            onChangedType() {
                this.form.url = null;
                this.form.menu_itemable_id = null;
            }
        },
    };
</script>
