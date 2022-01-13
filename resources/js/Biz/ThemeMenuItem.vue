<template>
    <div class="panel-block p-4 has-background-white">
        <div class="level">
            <div
                class="level-left"
                :class="isChild ? 'pl-4' : ''"
            >
                <span class="panel-icon handle-menu">
                    <i
                        class="fas fa-bars"
                        aria-hidden="true"
                    />
                </span>
                <span
                    v-if="menuItem.children.length > 0"
                    class="panel-icon"
                >
                    <i
                        class="fas fa-caret-down"
                        aria-hidden="true"
                    />
                </span>

                {{ menuItem.title }}

                <biz-tag
                    v-for="translation in menuItem.translations"
                    :key="translation.id"
                    class="is-info px-2 ml-1 is-small"
                >
                    {{ translation.locale?.toUpperCase() }}
                </biz-tag>
            </div>

            <div class="level-right">
                <biz-button
                    class="is-ghost has-text-black"
                    type="button"
                    @click.prevent="$emit('duplicate-menu-item', menuItem)"
                >
                    <span class="icon is-small">
                        <i class="far fa-copy" />
                    </span>
                </biz-button>

                <biz-button
                    class="is-ghost has-text-black"
                    type="button"
                    @click="$emit('edit-row', menuItem)"
                >
                    <span class="icon is-small">
                        <i class="fas fa-pen" />
                    </span>
                </biz-button>

                <biz-button
                    class="is-ghost has-text-black ml-1"
                    type="button"
                    @click="$emit('delete-row', menuItemIndex)"
                >
                    <span class="icon is-small">
                        <i class="far fa-trash-alt" />
                    </span>
                </biz-button>
            </div>
        </div>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizTag from '@/Biz/Tag';

    export default {
        name: 'ThemeMenuItem',

        components: {
            BizButton,
            BizTag,
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
    };
</script>

<style scoped>
    .handle-menu {
        cursor: pointer;
    }

    .level {
        width: 100%;
    }
</style>
