<template>
    <div class="card">
        <div class="card-content p-2">
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <div class="buttons">
                            <biz-button-icon
                                type="button"
                                class="handle-menu"
                                :title="i18n.drag_and_drop"
                                :icon="icon.move"
                            />

                            <biz-button-icon
                                type="button"
                                :icon="icon.up"
                                :disabled="isUpButtonDisabled"
                                @click="$emit('move-menu-item', 'up', menuItemIndex)"
                            />

                            <biz-button-icon
                                type="button"
                                :icon="icon.down"
                                :disabled="isDownButtonDisabled"
                                @click="$emit('move-menu-item', 'down', menuItemIndex)"
                            />
                        </div>
                    </div>

                    <div class="level-item">
                        {{ menuItem.title }}

                        <biz-tag
                            v-for="translation in menuItem.translations"
                            :key="translation.id"
                            class="is-info px-2 ml-1 is-small"
                        >
                            {{ translation.locale?.toUpperCase() }}
                        </biz-tag>
                    </div>
                </div>

                <div class="level-right">
                    <div class="level-item">
                        <div class="buttons">
                            <biz-button-icon
                                v-if="isAddButtonEnabled"
                                :icon="icon.add"
                                icon-class="is-small"
                                type="button"
                                class="is-ghost has-text-black"
                                @click.prevent="$emit('add-child-menu-item', menuItemIndex)"
                            />

                            <biz-button-icon
                                :icon="icon.copy"
                                icon-class="is-small"
                                type="button"
                                class="is-ghost has-text-black"
                                @click.prevent="$emit('duplicate-menu-item', menuItem)"
                            />

                            <biz-button-icon
                                :icon="icon.edit"
                                icon-class="is-small"
                                type="button"
                                class="is-ghost has-text-black"
                                @click="$emit('edit-row', menuItem)"
                            />

                            <biz-button-icon
                                :icon="icon.remove"
                                icon-class="is-small"
                                type="button"
                                class="is-ghost has-text-black"
                                @click="$emit('delete-row', menuItemIndex)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasTranslation from '@/Mixins/HasTranslation';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizTag from '@/Biz/Tag.vue';
    import {
        move,
        up,
        down,
        add,
        copy,
        edit,
        remove,
    } from '@/Libs/icon-class';

    export default {
        name: 'ThemeMenuItem',

        components: {
            BizTag,
            BizButtonIcon,
        },

        mixins: [
            MixinHasTranslation,
        ],

        props:{
            isChild: {
                type: Boolean,
                default: false
            },
            localeOptions: {
                type: Array,
                default:() => {},
            },
            menuItem: {
                type: Object,
                required: true,
            },
            menuItemIndex: {
                type: Number,
                required: true,
            },
            selectedLocale: {
                type: String,
                default: "en"
            },
            isUpButtonDisabled: { type: Boolean, default: false },
            isDownButtonDisabled: { type: Boolean, default: false },
            isAddButtonEnabled: { type: Boolean, default: false },
        },

        emits: [
            'add-child-menu-item',
            'delete-row',
            'duplicate-menu-item',
            'edit-row',
            'move-menu-item',
        ],

        setup() {
            return {
                icon: {
                    move,
                    up,
                    down,
                    add,
                    copy,
                    edit,
                    remove,
                },
            };
        },
    };
</script>
