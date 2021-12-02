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
                        v-model="setLocale"
                        label="Language"
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
                        v-model="setGroup"
                        label="Group"
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
                :links="records.links"
                :query-params="queryParams"
            />
        </div>
    </app-layout>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import AppLayout from '@/Layouts/AppLayout';
    import SdbButton from '@/Sdb/Button';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbField from '@/Sdb/Field';
    import SdbFlashNotifications from '@/Sdb/FlashNotifications';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import SdbInput from '@/Sdb/Input';
    import SdbPagination from '@/Sdb/Pagination';
    import { merge, debounce } from 'lodash';
    import { ref } from 'vue';
    import { success as successAlert, confirmDelete, confirmLeaveProgress } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbErrorNotifications,
            SdbField,
            SdbFlashNotifications,
            SdbFormSelect,
            SdbInput,
            SdbPagination,
        },

        mixins: [
            MixinFilterDataHandle,
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
            };
        },

        data() {
            return {
                selectedIndex: null,
            };
        },

        computed: {
            setLocale: {
                get() {
                    return this.locale;
                },

                set(val) {
                    const oldData = this.locale;
                    if (this.form.isDirty) {
                        confirmLeaveProgress().then((result) => {
                            if (result.isConfirmed) {
                                this.locale = val;
                                this.search();
                            } else {
                                this.locale = val;
                                setTimeout(() => {
                                    this.locale = oldData;
                                }, 200);
                            }
                        });
                    } else {
                        this.locale = val;
                        this.search();
                    }
                },
            },

            setGroup: {
                get() {
                    return this.group;
                },

                set(val) {
                    const oldData = this.group;
                    if (this.form.isDirty) {
                        confirmLeaveProgress().then((result) => {
                            if (result.isConfirmed) {
                                this.group = val;
                                this.search();
                            } else {
                                this.group = val;
                                setTimeout(() => {
                                    this.group = oldData;
                                }, 200);
                            }
                        });
                    } else {
                        this.group = val;
                        this.search();
                    }
                },
            },
        },


        methods: {
            getUseForm() {
                return useForm({
                    translations: this.records.data,
                });
            },

            search: debounce(function() {
                this.queryParams['group'] = this.group;
                this.queryParams['locale'] = this.locale;
                this.refreshWithQueryParams();
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

                this.form.post(route(this.baseRouteName+'.update'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.form = this.getUseForm();
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
        },
    }
</script>
