<template>
    <div>
        <div class="box">
            <edit-tabs
                :tabs="tabs"
                :tab-index="activeTab"
            />

            <div class="columns">
                <div class="column is-10">
                    <table class="table is-bordered is-striped is-fullwidth">
                        <thead>
                            <tr>
                                <th class="has-text-centered">
                                    {{ i18n.title }} (Draggable)
                                </th>
                                <th class="has-text-centered">
                                    {{ i18n.actions }}
                                </th>
                            </tr>
                        </thead>

                        <draggable
                            v-model="form.navigations"
                            group="navigation"
                            tag="tbody"
                            item-key="route"
                        >
                            <template #item="{element, index}">
                                <tr class="p-2">
                                    <td>
                                        <div class="level">
                                            <div class="level-left">
                                                <biz-button-icon
                                                    title="Drag"
                                                    type="button"
                                                    :icon="icon.bars"
                                                >
                                                    <span class="ml-2">
                                                        {{ index + 1 }}
                                                    </span>
                                                </biz-button-icon>
                                            </div>
                                            <div class="level-item">
                                                <biz-input v-model="element.title" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="level-right">
                                            <div class="buttons">
                                                <biz-button-icon
                                                    title="Up"
                                                    :disabled="!canMoveUp(index)"
                                                    :icon="icon.up"
                                                    @click.prevent="moveUp(index)"
                                                />

                                                <biz-button-icon
                                                    title="Down"
                                                    :disabled="!canMoveDown(index)"
                                                    :icon="icon.down"
                                                    @click.prevent="moveDown(index)"
                                                />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </draggable>
                    </table>
                    <div class="columns">
                        <div class="column has-text-right">
                            <biz-button-icon
                                class="is-primary"
                                :icon="icon.floppyDisk"
                                @click.prevent="submit"
                            >
                                <span>{{ i18n.save }}</span>
                            </biz-button-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizInput from '@/Biz/Input.vue';
    import EditTabs from './EditTabs.vue';
    import Draggable from 'vuedraggable';
    import { confirmDelete, confirm, success as successAlert } from '@/Libs/alert';
    import { computed, ref, onMounted } from 'vue';
    import { useForm, usePage } from '@inertiajs/vue3';
    import { bars, down, up, floppyDisk } from '@/Libs/icon-class.js';

    export default {
        name: 'ModuleEditNavigation',

        components: {
            BizButtonIcon,
            BizInput,
            EditTabs,
            Draggable,
        },

        mixins: [
            MixinHasLoader,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            navigations: { type: Array, required: true },
            tabIndex: { type: Number, default: 0 },
            tabs: { type: Array, required: true },
            moduleId: { type: Number, required: true },
        },

        setup(props) {
            const form = useForm({ navigations: [] });

            onMounted(() => {
                form.navigations = props.navigations;
            });

            return {
                activeTab: props.tabIndex,
                form,
                i18n: usePage().props.i18n,
                icon: { bars, down, up, floppyDisk },
                navigationsLength: computed(() => form.navigations.length),
            };
        },

        methods: {
            swapElements(index1, index2) {
                const navigations = this.form.navigations;
                navigations[index1] = navigations.splice(index2, 1, navigations[index1])[0];
            },

            canMoveUp(currentIndex) {
                return currentIndex > 0;
            },

            canMoveDown(currentIndex) {
                return (this.navigationsLength - 1) > currentIndex;
            },

            moveUp(currentIndex) {
                this.swapElements(currentIndex, currentIndex - 1);
            },

            moveDown(currentIndex) {
                this.swapElements(currentIndex, currentIndex + 1);
            },

            submit() {
                const url = route(this.baseRouteName + 'navigations.update', this.moduleId);
                this.form.put(url, {
                    onStart: this.onStartLoadingOverlay,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError: () => { oopsAlert() },
                    onFinish: this.onEndLoadingOverlay,
                });
            },
        }
    };
</script>
