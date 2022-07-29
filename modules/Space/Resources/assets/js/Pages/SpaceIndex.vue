<template>
    <app-layout :title="title">
        <template #header>
            {{ title }}
        </template>

        <div class="box">
            <div class="columns">
                <div class="column">
                    <div class="is-pulled-left">
                        <!--
                        <biz-filter-search
                            v-model="term"
                            @search="search"
                        />
                        -->
                    </div>

                    <biz-dropdown
                        :close-on-click="false"
                    >
                        <template #trigger>
                            <span>
                                {{ selectedParent ? selectedParent.value : 'Select Space' }}
                            </span>

                            <span class="icon is-small">
                                <i
                                    :class="icon.angleDown"
                                    aria-hidden="true"
                                />
                            </span>
                        </template>

                        <biz-dropdown-scroll
                            :max-height="300"
                        >
                            <biz-dropdown-item
                                v-for="option in parentOptions"
                                :key="option.id"
                                @click="parentId = option.id"
                            >
                                {{ option.value }}
                            </biz-dropdown-item>
                        </biz-dropdown-scroll>
                    </biz-dropdown>
                </div>

                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button-link
                            :href="route('admin.spaces.create')"
                            class="is-primary"
                        >
                            <span class="icon is-small">
                                <i :class="icon.add" />
                            </span>
                            <span>Create New</span>
                        </biz-button-link>
                    </div>
                </div>
            </div>

            <div class="space-list">
                <nested-draggable
                    class="px-2 py-4"
                    :spaces="spaces"
                    :disabled="!isSortableEnabled"
                    @on-end="onEnd"
                    @delete-row="deleteSpace"
                />
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizDropdownScroll from '@/Biz/DropdownScroll';
    import icon from '@/Libs/icon-class';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import NestedDraggable from './NestedDraggable';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            BizButtonLink,
            BizDropdown,
            BizDropdownItem,
            BizDropdownScroll,
            NestedDraggable,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            baseRouteName: { type: String, required: true },
            isSortableEnabled: { type: Boolean, default: true },
            parent: { type: [Object, null], required: true },
            parentOptions: { type: Array, required: true },
            spaces: { type: Array, required: true },
            title: { type: String, default: "" },
        },

        setup(props) {
            return {
                form: useForm({
                    spaces: props.spaces,
                }),
            };
        },

        data() {
            return {
                parentId: this.parent,
                icon,
            };
        },

        computed: {
            selectedParent() {
                return this.parentOptions.find(option => option.id == this.parentId);
            },
        },

        watch: {
            parentId(newParentId) {
                const url = route('admin.spaces.index', {parent: newParentId});
                this.$inertia.visit(url);
            },
        },

        methods: {
            onEnd(evt) {
                const toParent = evt.to.dataset.parent;
                const current = evt.item.dataset.id

                if (current) {
                    const url = route('admin.spaces.move-node', {
                        current: current,
                        parent: toParent
                    });

                    this.$inertia.post(url, {
                        replace: true,
                    });
                }
            },

            deleteSpace(space) {
                const self = this;

                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(this.baseRouteName+'.destroy', space.id),
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: () => {
                                    oopsAlert();
                                },
                                onSuccess: (page) => {
                                    successAlert(page.props.flash.message);
                                },
                            }
                        );
                    }
                })
            },
        },
    };
</script>
