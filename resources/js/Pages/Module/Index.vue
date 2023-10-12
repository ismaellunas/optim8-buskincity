<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column is-4 is-offset-8 has-text-right">
                    <biz-button-icon
                        class="is-primary"
                        :disabled="!form.isDirty"
                        :icon="icon.floppyDisk"
                        @click.prevent="saveOrder"
                    >
                        <span>{{ i18n.save }}</span>
                    </biz-button-icon>
                </div>
            </div>

            <biz-table-index
                :records="records"
                :query-params="queryParams"
            >
                <template #thead>
                    <tr>
                        <th>{{ i18n.name }}</th>
                        <th>{{ i18n.status }}</th>
                        <th>
                            <div class="level-right">
                                {{ i18n.actions }}
                            </div>
                        </th>
                    </tr>
                </template>

                <tr
                    v-for="(record, index) in form.modules"
                    :key="record.id"
                >
                    <td>{{ capitalCase(record.displayTitle) ?? '-' }}</td>
                    <td>
                        <biz-tag
                            :class="{ 'is-primary': record.is_active, 'is-warning': !record.is_active }"
                        >
                            {{ record.displayStatus ?? '-' }}
                        </biz-tag>
                    </td>
                    <td>
                        <div class="level-right">
                            <div class="buttons">
                                <div class="mr-5">
                                    <biz-button-icon
                                        v-if="canMoveUp(index)"
                                        class="is-info"
                                        title="Up"
                                        :icon="icon.up"
                                        @click.prevent="moveUp(index)"
                                    />

                                    <biz-button-icon
                                        v-if="canMoveDown(index)"
                                        class="is-info"
                                        title="Down"
                                        :icon="icon.down"
                                        @click.prevent="moveDown(index)"
                                    />
                                </div>

                                <div>
                                    <biz-button-link
                                        class="has-text-black"
                                        :href="route(baseRouteName+'edit', {id: record.id})"
                                    >
                                        <biz-icon
                                            class="is-small"
                                            :icon="icon.edit"
                                        />
                                    </biz-button-link>

                                    <biz-button-icon
                                        class="has-text-black"
                                        :icon="record.is_active ? icon.toggleOn : icon.toggleOff"
                                        :title="record.is_active ? i18n.deactivate : i18n.activate"
                                        @click.prevent="confirmActivation(record)"
                                    />
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </biz-table-index>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import BizTag from '@/Biz/Tag.vue';
    import { bars, down, up, edit as iconEdit, remove as iconRemove, floppyDisk, toggleOn, toggleOff } from '@/Libs/icon-class';
    import { capitalCase } from 'change-case';
    import { computed, reactive, ref, onMounted } from 'vue';
    import { confirmDelete, confirm, success as successAlert } from '@/Libs/alert';
    import { useForm, usePage, router } from '@inertiajs/vue3';

    export default {
        name: 'ModuleIndex',

        components: {
            BizButtonIcon,
            BizButtonLink,
            BizTableIndex,
            BizIcon,
            BizTag,
        },

        mixins: [
            MixinHasLoader,
        ],

        layout: AppLayout,

        props: {
            records: { type: Object, required: true },
            baseRouteName: { type: String, default: 'admin.settings.modules.' },
        },

        setup(props) {
            const queryParams = computed(() => props.pageQueryParams);

            const form = useForm({ modules: [] });

            onMounted(() => {
                form.modules = props.records.data;
            });

            return {
                queryParams,
                term: ref(queryParams.value?.term ?? null),
                i18n: computed(() => usePage().props.i18n),
                form,
                icon: {
                    down,
                    edit: iconEdit,
                    floppyDisk,
                    remove: iconRemove,
                    toggleOff,
                    toggleOn,
                    up,
                },
                capitalCase,
            };
        },

        computed: {
            modules: {
                get() {
                    return this.form.modules;
                },
                set(newValue) {
                    this.form.modules = newValue;
                },
            },
        },

        watch: {
            'records.data'(newValue) {
                this.modules = newValue;
            },
        },

        methods: {
            confirmActivation(module) {
                const action = module.is_active ? 'deactivate' : 'activate';

                confirm('Activation', 'Are you sure you want to ' + action)
                    .then((result) => {
                        if (result.isConfirmed) {
                            this.activation(module, action);
                        }
                    });
            },

            activation(module, action) {
                const url = route(this.baseRouteName + action, {id: module.id});

                router.post(url, {}, {
                    onStart: this.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: this.onEndLoadingOverlay,
                });
            },

            saveOrder() {
                const modules = this.form.modules.map((module, index) => {
                    return {
                        id: module.id,
                        order: index,
                    };
                });

                this
                    .form
                    .transform((data) => ({
                        ...data,
                        ...{ modules }
                    }))
                    .post(route(this.baseRouteName + 'update-order'), {
                        onStart: this.onStartLoadingOverlay,
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                        },
                        onError: () => { oopsAlert() },
                        onFinish: this.onEndLoadingOverlay,
                    });
            },

            canMoveUp(currentIndex) {
                return currentIndex > 0;
            },

            canMoveDown(currentIndex) {
                return (this.form.modules.length - 1) > currentIndex;
            },

            swapElements(index1, index2) {
                const modules = this.form.modules;
                modules[index1] = modules.splice(index2, 1, modules[index1])[0];
            },

            moveUp(currentIndex) {
                this.swapElements(currentIndex, currentIndex - 1);
            },

            moveDown(currentIndex) {
                this.swapElements(currentIndex, currentIndex + 1);
            },
        }
    };
</script>
