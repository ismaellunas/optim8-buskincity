<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <sdb-flash-notifications :flash="$page.props.flash" />
        <sdb-error-notifications
            :bags="['default']"
            :errors="$page.props.errors"
        />

        <div class="box">
            <div class="columns">
                <div class="column">
                    <sdb-dropdown
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

                        <sdb-dropdown-scroll :max-height="350">
                            <sdb-dropdown-item
                                v-for="(localeOption, index) in localeOptions"
                                :key="index"
                                class="pt-0 pb-1"
                            >
                                <sdb-button
                                    :class="[
                                        'is-fullwidth',
                                        (localeOption.id == locale) ? 'is-link' : 'is-white',
                                    ]"
                                    @click="filterLocale(localeOption)"
                                >
                                    {{ localeOption.name }}
                                </sdb-button>
                            </sdb-dropdown-item>
                        </sdb-dropdown-scroll>
                    </sdb-dropdown>

                    <sdb-dropdown
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

                        <sdb-dropdown-scroll :max-height="350">
                            <sdb-dropdown-item
                                v-for="(groupOption, index) in groupOptions"
                                :key="index"
                            >
                                <sdb-checkbox
                                    v-model:checked="groups"
                                    :value="groupOption"
                                    @change="filterGroups"
                                >
                                    &nbsp; {{ groupOption }}
                                </sdb-checkbox>
                            </sdb-dropdown-item>
                        </sdb-dropdown-scroll>
                    </sdb-dropdown>
                </div>

                <div class="column">
                    <div class="is-pulled-right">
                        <sdb-button-download
                            :url="route('admin.settings.translation-manager.export', {locale: locale, groups: groups})"
                            class="mr-2"
                        >
                            Export
                        </sdb-button-download>

                        <sdb-button
                            class="mr-2"
                            @click="openModal"
                        >
                            Import
                        </sdb-button>

                        <sdb-button
                            class="is-link"
                            @click="onSubmit"
                        >
                            Update
                        </sdb-button>
                    </div>
                </div>
            </div>
            <div class="table-container">
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
                            <td>{{ page.key }}</td>
                            <td v-if="!isReferenceLanguage">
                                {{ page.en_value ?? "-" }}
                            </td>
                            <td>
                                <form
                                    action="post"
                                    @submit.prevent="onSubmit"
                                >
                                    <sdb-field class="mb-0">
                                        <div class="control is-expanded">
                                            <sdb-input
                                                v-model="form.translations[index].value"
                                                placeholder="value"
                                            />
                                        </div>
                                    </sdb-field>
                                </form>
                            </td>
                            <td>
                                <div class="level-right">
                                    <sdb-button
                                        v-if="page.value"
                                        class="is-ghost has-text-black ml-1"
                                        @click="onClear(index)"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-eraser" />
                                        </span>
                                    </sdb-button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <sdb-pagination
                :is-ajax="true"
                :links="records.links"
                :query-params="queryParams"
                @on-clicked-pagination="onClickedPagination"
            />
        </div>

        <sdb-modal-card
            v-show="isModalOpen"
            @close="closeModal()"
        >
            <template #header>
                <p class="modal-card-title">
                    Import
                </p>

                <sdb-button
                    aria-label="close"
                    class="delete is-primary"
                    type="button"
                    @click="closeModal"
                />
            </template>

            <form @submit.prevent="submitImport">
                <div class="control">
                    <sdb-form-file
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
                    </sdb-form-file>
                </div>
            </form>

            <template #footer>
                <div class="column p-0">
                    <div class="buttons is-right">
                        <sdb-button
                            class="is-link"
                            @click="submitImport"
                        >
                            Submit
                        </sdb-button>

                        <sdb-button
                            class="is-link is-light ml-2"
                            type="button"
                            @click="closeModal()"
                        >
                            Cancel
                        </sdb-button>
                    </div>
                </div>
            </template>
        </sdb-modal-card>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonDownload from '@/Sdb/ButtonDownload';
    import SdbCheckbox from '@/Sdb/Checkbox';
    import SdbDropdown from '@/Sdb/Dropdown';
    import SdbDropdownItem from '@/Sdb/DropdownItem';
    import SdbDropdownScroll from '@/Sdb/DropdownScroll';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbField from '@/Sdb/Field';
    import SdbFlashNotifications from '@/Sdb/FlashNotifications';
    import SdbFormFile from '@/Sdb/Form/File';
    import SdbInput from '@/Sdb/Input';
    import SdbModalCard from '@/Sdb/ModalCard';
    import SdbPagination from '@/Sdb/Pagination';
    import { merge, debounce } from 'lodash';
    import { ref } from 'vue';
    import { success as successAlert, confirmDelete, confirmLeaveProgress } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonDownload,
            SdbCheckbox,
            SdbDropdown,
            SdbDropdownItem,
            SdbDropdownScroll,
            SdbErrorNotifications,
            SdbField,
            SdbFlashNotifications,
            SdbFormFile,
            SdbInput,
            SdbModalCard,
            SdbPagination,
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
                type: Array,
                default:() => [],
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
                            this.refreshWithQueryParams();
                        } else {
                            this.groups = this.queryParams['groups'] ?? [];
                            this.locale = this.queryParams['locale'] ?? this.defaultLocale;
                        }
                    });
                } else {
                    this.queryParams['groups'] = this.groups;
                    this.queryParams['locale'] = this.locale;
                    this.refreshWithQueryParams();
                }
            },

            filterLocale: debounce(function(locale) {
                this.selectedLocale = locale;
                this.search();
            }, 750),

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
                    route("admin.settings.translation-manager.import"),
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
