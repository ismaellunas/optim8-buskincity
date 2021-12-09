<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <sdb-flash-notifications :flash="$page.props.flash" />
        <sdb-error-notifications :errors="$page.props.errors" />

        <div class="box">
            <div class="columns">
                <div class="column">
                    <sdb-form-select
                        v-model="locale"
                        label="Language"
                        @change="search()"
                    >
                        <option
                            v-for="localeOption in localeOptions"
                            :key="localeOption.id"
                            :value="localeOption.id"
                        >
                            {{ localeOption.name }}
                        </option>
                    </sdb-form-select>

                    <sdb-form-select
                        v-model="group"
                        label="Group"
                        @change="search()"
                    >
                        <option value="">
                            All
                        </option>
                        <option
                            v-for="(groupOption, index) in groupOptions"
                            :key="index"
                            :value="groupOption"
                        >
                            {{ groupOption }}
                        </option>
                    </sdb-form-select>
                </div>
                <div class="column">
                    <div class="is-pulled-right">
                        <sdb-button-download
                            :url="route('admin.settings.translation-manager.export', [locale, group])"
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
                            <th v-if="locale !== defaultLocale">
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
                            <td v-if="locale !== defaultLocale">
                                {{ page.en_value ?? "-" }}
                            </td>
                            <td>
                                <template v-if="selectedIndex !== index">
                                    {{ page.value ?? "-" }}
                                </template>

                                <template v-else>
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
                                </template>
                            </td>
                            <td>
                                <div class="level-right">
                                    <sdb-button
                                        class="is-ghost has-text-black"
                                        @click="setSelectedIndex(index)"
                                    >
                                        <span
                                            v-if="selectedIndex !== index"
                                            class="icon is-small"
                                        >
                                            <i class="fas fa-pen" />
                                        </span>

                                        <span
                                            v-else
                                            class="icon is-small"
                                        >
                                            <i class="fas fa-times" />
                                        </span>
                                    </sdb-button>
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
            <form @submit.prevent="submitImport">
                <div class="control">
                    <sdb-form-file
                        v-model="importForm.file"
                        :accepted-types="acceptedTypes"
                        label="Import File"
                        required
                    >
                        <template
                            v-if="texts.fileInputNotes"
                            #note
                        >
                            <p class="help is-info">
                                <ul>
                                    <li
                                        v-for="note in texts.fileInputNotes"
                                        :key="note"
                                    >
                                        {{ note }}
                                    </li>
                                </ul>
                            </p>
                        </template>
                    </sdb-form-file>
                </div>

                <div class="field is-grouped is-pulled-right mt-4">
                    <sdb-button class="is-link">
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
            </form>
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
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbField from '@/Sdb/Field';
    import SdbFlashNotifications from '@/Sdb/FlashNotifications';
    import SdbFormFile from '@/Sdb/Form/File';
    import SdbFormSelect from '@/Sdb/Form/Select';
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
            SdbErrorNotifications,
            SdbField,
            SdbFlashNotifications,
            SdbFormFile,
            SdbFormSelect,
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
            }
        },

        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            return {
                group: ref(props.pageQueryParams?.group ?? ""),
                locale: ref(props.pageQueryParams?.locale ?? props.defaultLocale),
                queryParams: ref(queryParams),
                form: useForm({
                    translations: props.records.data,
                }),
                importForm: useForm({
                    file: null
                }),
            };
        },

        data() {
            return {
                selectedIndex: null,
                isProcessing: false,
                acceptedTypes: ['.csv'],
            };
        },

        methods: {
            getUseForm() {
                return useForm({
                    translations: this.records.data,
                });
            },

            search: debounce(function() {
                if (this.form.isDirty) {
                    confirmLeaveProgress().then((result) => {
                        if (result.isConfirmed) {
                            this.queryParams['group'] = this.group;
                            this.queryParams['locale'] = this.locale;
                            this.refreshWithQueryParams();
                        } else {
                            this.group = this.queryParams['group'] ?? "";
                            this.locale = this.queryParams['locale'] ?? this.defaultLocale;
                        }
                    });
                } else {
                    this.queryParams['group'] = this.group;
                    this.queryParams['locale'] = this.locale;
                    this.refreshWithQueryParams();
                }
            }, 750),

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
                            this.selectedIndex = null;
                        },
                    }
                );
            },

            setSelectedIndex(index) {
                if (this.selectedIndex === null || this.selectedIndex !== index) {
                    this.selectedIndex = index;
                } else {
                    this.selectedIndex = null;
                }
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
                        self.selectedIndex = null;
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
                            this.selectedIndex = null;
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
                        },
                        onFinish: () => {
                            self.loader.hide();
                            self.isProcessing = false;
                            self.closeModal();
                        },
                    }
                );
            },
        },
    };
</script>
