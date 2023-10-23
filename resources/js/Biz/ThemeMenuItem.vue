<template>
    <div class="card">
        <div class="card-content p-2">
            <div class="level">
                <div class="level-left">
                    <div class="level-item handle-menu is-clickable">
                        <biz-icon :icon="icon.bars" />
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
                        <biz-button
                            class="is-ghost has-text-black"
                            type="button"
                            @click.prevent="$emit('duplicate-menu-item', menuItem)"
                        >
                            <span class="icon is-small">
                                <i :class="icon.copy" />
                            </span>
                        </biz-button>

                        <biz-button
                            class="is-ghost has-text-black"
                            type="button"
                            @click="$emit('edit-row', menuItem)"
                        >
                            <span class="icon is-small">
                                <i :class="icon.edit" />
                            </span>
                        </biz-button>

                        <biz-button
                            class="is-ghost has-text-black ml-1"
                            type="button"
                            @click="$emit('delete-row', menuItemIndex)"
                        >
                            <span class="icon is-small">
                                <i :class="icon.remove" />
                            </span>
                        </biz-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizTag from '@/Biz/Tag.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import icon from '@/Libs/icon-class';

    export default {
        name: 'ThemeMenuItem',

        components: {
            BizButton,
            BizTag,
            BizIcon,
        },

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
        },

        emits: [
            'delete-row',
            'duplicate-menu-item',
            'edit-row',
        ],

        data() {
            return {
                icon,
            };
        },
    };
</script>
