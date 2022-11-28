<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column">
                    <biz-dropdown
                        :close-on-click="false"
                    >
                        <template #trigger>
                            <span>
                                {{ selectedParent ? selectedParent.value : 'Select Space' }}
                            </span>

                            <biz-icon :icon="icon.angleDown" />
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

                <div class="column has-text-right">
                    <biz-button-link
                        class="is-primary"
                        :href="route('admin.spaces.create')"
                    >
                        <biz-icon :icon="icon.add" />
                        <span>Create New</span>
                    </biz-button-link>
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
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizDropdownScroll from '@/Biz/DropdownScroll';
    import BizIcon from '@/Biz/Icon';
    import icon from '@/Libs/icon-class';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import NestedDraggable from './NestedDraggable';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';

    export default {
        components: {
            BizButtonLink,
            BizDropdown,
            BizDropdownItem,
            BizDropdownScroll,
            BizIcon,
            NestedDraggable,
        },

        mixins: [
            MixinHasLoader,
        ],

        layout: AppLayout,

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
