<template>
    <div>
        <biz-error-notifications
            :bags="['default']"
            :errors="$page.props.errors"
        />

        <div class="box">
            <div class="columns">
                <div class="column">
                    <div class="buttons is-right">
                        <biz-button-download
                            :url="route(baseRouteName + '.export', {locale: locale, groups: groups})"
                            class="export-translation mr-2"
                        >
                            {{ i18n.export }}
                        </biz-button-download>

                        <biz-button
                            class="import-translation mr-2"
                            @click="openModal"
                        >
                            {{ i18n.import }}
                        </biz-button>

                        <biz-button-link
                            :href="route(baseRouteName + '.create')"
                            class="create-translation is-primary mr-2"
                        >
                            <biz-icon :icon="icon.add" />
                            <span>{{ i18n.add_new }}</span>
                        </biz-button-link>

                        <biz-button-icon
                            class="update-translation is-link"
                            :icon="icon.edit"
                            @click="onSubmit"
                        >
                            <span>{{ i18n.update }}</span>
                        </biz-button-icon>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column is-8">
                    <biz-dropdown
                        :close-on-click="true"
                    >
                        <template #trigger>
                            <span>{{ selectedLocale ?? 'Language' }}</span>

                            <span class="icon is-small">
                                <i
                                    :class="icon.angleDown"
                                    aria-hidden="true"
                                />
                            </span>
                        </template>

                        <biz-dropdown-scroll :max-height="350">
                            <biz-dropdown-item
                                v-for="(localeOption, index) in localeOptions"
                                :key="index"
                                class="pt-0 pb-1"
                            >
                                <biz-button
                                    :class="[
                                        'is-fullwidth',
                                        (localeOption.id == locale) ? 'is-link' : 'is-white',
                                    ]"
                                    @click="filterLocale(localeOption)"
                                >
                                    {{ localeOption.name }}
                                </biz-button>
                            </biz-dropdown-item>
                        </biz-dropdown-scroll>
                    </biz-dropdown>

                    <biz-dropdown
                        class="group-filter ml-3"
                        :close-on-click="false"
                    >
                        <template #trigger>
                            <span>{{ i18n.group }}</span>

                            <span
                                v-if="groups.length > 0"
                                class="ml-1"
                            >
                                ({{ groups.length }})
                            </span>
                            <span class="icon is-small">
                                <i
                                    :class="icon.angleDown"
                                    aria-hidden="true"
                                />
                            </span>
                        </template>

                        <biz-dropdown-scroll :max-height="350">
                            <biz-dropdown-item
                                v-for="(groupOption, key, index) in groupOptions"
                                :key="index"
                            >
                                <biz-checkbox
                                    v-model:checked="groups"
                                    :value="key"
                                    @change="filterGroups"
                                >
                                    &nbsp; {{ groupOption }}
                                </biz-checkbox>
                            </biz-dropdown-item>
                        </biz-dropdown-scroll>
                    </biz-dropdown>
                </div>

                <div class="column is-4">
                    <biz-filter-search
                        v-model="term"
                        :placeholder="i18n.search"
                        @search="filterTerm"
                    />
                </div>
            </div>

            <form
                action="post"
                @submit.prevent="onSubmit"
            >
                <biz-table-index
                    :is-ajax-pagination="true"
                    :query-params="queryParams"
                    :records="records"
                    @on-clicked-pagination="onClickedPagination"
                >
                    <template #thead>
                        <tr>
                            <th>#</th>
                            <th>{{ i18n.group }}</th>
                            <th>{{ i18n.key }}</th>
                            <th v-if="!isReferenceLanguage">
                                English {{ i18n.value }}
                            </th>
                            <th>{{ i18n.value }}</th>
                            <th>
                                <div class="level-right">
                                    {{ i18n.actions }}
                                </div>
                            </th>
                        </tr>
                    </template>

                    <tr
                        v-for="(page, index) in records.data"
                        :key="page.id"
                    >
                        <th>{{ page.id ?? "-" }}</th>
                        <td>{{ page.group }}</td>
                        <td>
                            <template v-if="!page.group && isReferenceLanguage">
                                <biz-field class="mb-0">
                                    <div class="control is-expanded">
                                        <biz-input
                                            v-if="form.translations[index]"
                                            v-model="form.translations[index].key"
                                            placeholder="key"
                                        />
                                    </div>
                                </biz-field>
                            </template>

                            <template v-else>
                                {{ page.key }}
                            </template>
                        </td>
                        <td v-if="!isReferenceLanguage">
                            {{ page.en_value ?? "-" }}
                        </td>
                        <td>
                            <biz-field class="mb-0">
                                <div class="control is-expanded">
                                    <biz-textarea
                                        v-if="form.translations[index]"
                                        v-model="form.translations[index].value"
                                        placeholder="value"
                                        style="min-width: 250px"
                                        rows="3"
                                    />
                                </div>
                            </biz-field>
                        </td>
                        <td>
                            <div class="level-right">
                                <biz-button-icon
                                    v-if="page.value"
                                    type="button"
                                    class="is-ghost has-text-black ml-1"
                                    :icon="icon.eraser"
                                    @click.prevent="onClear(index)"
                                />

                                <biz-button-icon
                                    v-if="!page.group && referenceLocale == page.locale"
                                    type="button"
                                    class="is-ghost has-text-black ml-1"
                                    :icon="icon.remove"
                                    @click.prevent="onDelete(page)"
                                />
                            </div>
                        </td>
                    </tr>
                </biz-table-index>
            </form>
        </div>

        <biz-modal-card
            v-show="isModalOpen"
            class="import-modal"
            @close="closeModal()"
        >
            <template #header>
                <p class="modal-card-title">
                    {{ i18n.import }}
                </p>

                <biz-button
                    aria-label="close"
                    class="delete is-primary"
                    type="button"
                    @click="closeModal"
                />
            </template>

            <form @submit.prevent="submitImport">
                <div class="control">
                    <biz-form-file
                        v-model="importForm.file"
                        :accepted-types="acceptedTypes"
                        :message="error('file', bags.import)"
                        :label="i18n.file"
                        required
                    >
                        <template
                            v-if="instructions.fileInputNotes"
                            #note
                        >
                            <p class="help is-info">
                                <ul>
                                    <li
                                        v-for="note in instructions.fileInputNotes"
                                        :key="note"
                                    >
                                        {{ note }}
                                    </li>
                                </ul>
                            </p>
                        </template>
                    </biz-form-file>
                </div>
            </form>

            <template #footer>
                <div class="column p-0">
                    <div class="buttons is-right">
                        <biz-button
                            class="is-link"
                            @click="submitImport"
                        >
                            {{ i18n.submit }}
                        </biz-button>

                        <biz-button
                            class="is-link is-light ml-2"
                            type="button"
                            @click="closeModal()"
                        >
                            {{ i18n.cancel }}
                        </biz-button>
                    </div>
                </div>
            </template>
        </biz-modal-card>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonDownload from '@/Biz/ButtonDownload.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizDropdown from '@/Biz/Dropdown.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizDropdownScroll from '@/Biz/DropdownScroll.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizField from '@/Biz/Field.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizFormFile from '@/Biz/Form/File.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizInput from '@/Biz/Input.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import BizTextarea from '@/Biz/Textarea.vue';
    import { merge, debounce } from 'lodash';
    import { ref } from 'vue';
    import { success as successAlert, confirmDelete, confirmLeaveProgress } from '@/Libs/alert';
    import { debounceTime } from '@/Libs/defaults';
    import { useForm } from '@inertiajs/vue3';
    import icon from '@/Libs/icon-class';

    export default {
        name: 'TranslationManagerIndex',

        components: {
            BizButton,
            BizButtonDownload,
            BizButtonIcon,
            BizButtonLink,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizDropdownScroll,
            BizErrorNotifications,
            BizField,
            BizFilterSearch,
            BizFormFile,
            BizIcon,
            BizInput,
            BizModalCard,
            BizTableIndex,
            BizTextarea,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasModal,
            MixinHasPageErrors,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: {
                type: String,
                required: true,
            },
            defaultLocale: {
                type: String,
                required: true,
            },
            groupOptions: {
                type: Object,
                default:() => {},
            },
            referenceLocale: {
                type: String,
                required: true,
            },
            localeOptions: {
                type: Array,
                default:() => [],
            },
            pageQueryParams: {
                type: Object,
                default:() => {},
            },
            records: {
                type: Object,
                required: true,
            },
            title: {
                type: String,
                required: true,
            },
            instructions: {
                type: Object,
                default: () => {},
            },
            bags: {
                type: Object,
                default: () => {},
            },
            i18n: {
                type: Object,
                default: () => ({
                    export : 'Export',
                    import : 'Import',
                    add_new : 'Add new',
                    update : 'Update',
                    search : 'Search',
                    group : 'Group',
                    key : 'Key',
                    value : 'Value',
                    actions : 'Actions',
                    file : 'File',
                    submit : 'Submit',
                    cancel : 'Cancel',
                }),
            },
        },

        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            return {
                groups: ref(props.pageQueryParams?.groups ?? []),
                locale: ref(props.pageQueryParams?.locale ?? props.defaultLocale),
                term: ref(props.pageQueryParams?.term ?? ''),
                queryParams: ref(queryParams),
                importForm: useForm({
                    file: null
                }),
            };
        },

        data() {
            return {
                isProcessing: false,
                acceptedTypes: ['.csv'],
                editIndex: null,
                form: useForm({
                    translations: this.records.data,
                }),
                icon,
            };
        },

        computed: {
            isReferenceLanguage() {
                return this.referenceLocale === this.locale;
            },
            selectedLocale: {
                get() {
                    const selectedLocale = this
                        .localeOptions
                        .find((localeOption) => localeOption.id == this.locale);
                    return selectedLocale?.name ?? '';
                },
                set(locale) {
                    this.locale = locale.id;
                }
            },
        },

        methods: {
            getUseForm() {
                return useForm({
                    translations: this.records.data,
                });
            },

            search() {
                if (this.form.isDirty) {
                    confirmLeaveProgress().then((result) => {
                        if (result.isConfirmed) {
                            this.queryParams['groups'] = this.groups;
                            this.queryParams['locale'] = this.locale;
                            this.queryParams['term'] = this.term;
                            this.refreshWithQueryParams();
                        } else {
                            this.groups = this.queryParams['groups'] ?? [];
                            this.locale = this.queryParams['locale'] ?? this.defaultLocale;
                            this.term = this.queryParams['term'] ?? '';
                        }
                    });
                } else {
                    this.queryParams['groups'] = this.groups;
                    this.queryParams['locale'] = this.locale;
                    this.queryParams['term'] = this.term;
                    this.refreshWithQueryParams();
                }
            },

            filterTerm: debounce(function() {
                this.search();
            }, debounceTime),

            filterLocale: debounce(function(locale) {
                this.selectedLocale = locale;
                this.search();
            }, debounceTime),

            filterGroups: debounce(function() {
                this.search();
            }, 1400),

            refreshWithQueryParams() {
                this.$inertia.get(
                    route(this.baseRouteName+'.edit'),
                    this.queryParams,
                    {
                        replace: true,
                        preserveState: true,
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => {
                            this.onEndLoadingOverlay();
                            this.form = this.getUseForm();
                        },
                    }
                );
            },

            onSubmit() {
                const self = this;

                self.form.post(route(self.baseRouteName+'.update'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.onStartLoadingOverlay();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.form = self.getUseForm();
                    },
                    onFinish: () => {
                        self.onEndLoadingOverlay();
                    }
                });
            },

            onClear(index) {
                const self = this;
                confirmDelete(
                    "Are you sure?",
                    "The value will be cleared."
                ).then((result) => {
                    if (result.isConfirmed) {
                        self.form.translations[index].value = null;
                    }
                });
            },

            onDelete(translation) {
                const self = this;
                let message = 'It will delete all translation on another language.';

                confirmDelete(
                    'Are you sure?',
                    message
                ).then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', translation.id),
                            {
                                onSuccess: () => {
                                    self.form = self.getUseForm();
                                },
                            },
                        );
                    }
                });
            },

            onClickedPagination(url) {
                if (this.form.isDirty) {
                    confirmLeaveProgress().then((result) => {
                        if (result.isConfirmed) {
                            this.refreshPagination(url);
                        }
                    });
                } else {
                    this.refreshPagination(url);
                }
            },

            refreshPagination(url) {
                this.$inertia.get(
                    url,
                    this.queryParams,
                    {
                        replace: true,
                        preserveState: true,
                        onFinish: () => {
                            this.form = this.getUseForm();
                        },
                    }
                );
            },

            getImportForm() {
                return useForm({
                    file: null,
                });
            },

            onShownModal() {
                this.importForm = this.getImportForm();
            },

            submitImport() {
                const self = this;
                self.importForm.post(
                    route(self.baseRouteName + '.import'),
                    {
                        preserveScroll: false,
                        preserveState: true,
                        replace: true,
                        onStart: () => {
                            self.onStartLoadingOverlay();
                            self.isProcessing = true;
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                            self.importForm.isDirty = false;
                            self.importForm.reset();
                            self.closeModal();

                            self.form = this.getUseForm();
                        },
                        onError: (errors) => {
                            const hasError = Object
                                .keys(errors)
                                .includes(self.bags.import);

                            if (! hasError) {
                                self.closeModal();
                            }
                        },
                        onFinish: () => {
                            self.onEndLoadingOverlay();
                            self.isProcessing = false;
                        },
                    }
                );
            },
        },
    };
</script>
