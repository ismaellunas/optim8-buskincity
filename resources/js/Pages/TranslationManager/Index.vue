<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <biz-flash-notifications :flash="$page.props.flash" />
        <biz-error-notifications
            :bags="['default']"
            :errors="$page.props.errors"
        />

        <div class="box">
            <div class="columns">
                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button-download
                            :url="route(baseRouteName + '.export', {locale: locale, groups: groups})"
                            class="mr-2"
                        >
                            Export
                        </biz-button-download>

                        <biz-button
                            class="mr-2"
                            @click="openModal"
                        >
                            Import
                        </biz-button>

                        <biz-button-link
                            :href="route(baseRouteName + '.create')"
                            class="is-primary mr-2"
                        >
                            <span class="icon is-small">
                                <i class="fas fa-plus" />
                            </span>
                            <span>Add New</span>
                        </biz-button-link>

                        <biz-button
                            class="is-link"
                            @click="onSubmit"
                        >
                            Update
                        </biz-button>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column">
                    <biz-filter-search
                        v-model="term"
                        @search="filterTerm"
                    />
                </div>

                <div class="column">
                    <biz-dropdown
                        :close-on-click="true"
                    >
                        <template #trigger>
                            <span>{{ selectedLocale ?? 'Language' }}</span>

                            <span class="icon is-small">
                                <i
                                    class="fas fa-angle-down"
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
                        class="ml-3"
                        :close-on-click="false"
                    >
                        <template #trigger>
                            <span>Group</span>

                            <span
                                v-if="groups.length > 0"
                                class="ml-1"
                            >
                                ({{ groups.length }})
                            </span>
                            <span class="icon is-small">
                                <i
                                    class="fas fa-angle-down"
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
            </div>

            <div class="table-container">
                <form
                    action="post"
                    @submit.prevent="onSubmit"
                >
                    <table class="table is-striped is-hoverable is-fullwidth">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Group</th>
                                <th>Key</th>
                                <th v-if="!isReferenceLanguage">
                                    English Value
                                </th>
                                <th>Value</th>
                                <th>
                                    <div class="level-right">
                                        Actions
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
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
                                        <biz-button
                                            v-if="page.value"
                                            type="button"
                                            class="is-ghost has-text-black ml-1"
                                            @click.prevent="onClear(index)"
                                        >
                                            <span class="icon is-small">
                                                <i class="fas fa-eraser" />
                                            </span>
                                        </biz-button>

                                        <biz-button
                                            v-if="!page.group && referenceLocale == page.locale"
                                            type="button"
                                            class="is-ghost has-text-black ml-1"
                                            @click.prevent="onDelete(page)"
                                        >
                                            <span class="icon is-small">
                                                <i class="far fa-trash-alt" />
                                            </span>
                                        </biz-button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
            <biz-pagination
                :is-ajax="true"
                :links="records.links"
                :query-params="queryParams"
                @on-clicked-pagination="onClickedPagination"
            />
        </div>

        <biz-modal-card
            v-show="isModalOpen"
            @close="closeModal()"
        >
            <template #header>
                <p class="modal-card-title">
                    Import
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
                        label="File"
                        required
                    >
                        <template
                            v-if="i18n.fileInputNotes"
                            #note
                        >
                            <p class="help is-info">
                                <ul>
                                    <li
                                        v-for="note in i18n.fileInputNotes"
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
                            Submit
                        </biz-button>

                        <biz-button
                            class="is-link is-light ml-2"
                            type="button"
                            @click="closeModal()"
                        >
                            Cancel
                        </biz-button>
                    </div>
                </div>
            </template>
        </biz-modal-card>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button';
    import BizButtonDownload from '@/Biz/ButtonDownload';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizDropdownScroll from '@/Biz/DropdownScroll';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizField from '@/Biz/Field';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizFlashNotifications from '@/Biz/FlashNotifications';
    import BizFormFile from '@/Biz/Form/File';
    import BizInput from '@/Biz/Input';
    import BizModalCard from '@/Biz/ModalCard';
    import BizPagination from '@/Biz/Pagination';
    import BizTextarea from '@/Biz/Textarea';
    import { merge, debounce } from 'lodash';
    import { ref } from 'vue';
    import { success as successAlert, confirmDelete, confirmLeaveProgress } from '@/Libs/alert';
    import { debounceTime } from '@/Libs/defaults';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonLink,
            BizButtonDownload,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizDropdownScroll,
            BizErrorNotifications,
            BizField,
            BizFilterSearch,
            BizFlashNotifications,
            BizFormFile,
            BizInput,
            BizModalCard,
            BizPagination,
            BizTextarea,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasModal,
            MixinHasPageErrors,
        ],

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
            i18n: {
                type: Object,
                default: () => {},
            },
            bags: {
                type: Object,
                default: () => {},
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
                        self.loader = self.$loading.show();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.form = self.getUseForm();
                    },
                    onFinish: () => {
                        self.loader.hide();
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
                            self.loader = self.$loading.show();
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
                            self.loader.hide();
                            self.isProcessing = false;
                        },
                    }
                );
            },
        },
    };
</script>
