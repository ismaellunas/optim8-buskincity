<template>
    <div class="space-item box panel-block has-background-white mb-0">
        <div class="level">
            <div class="level-left">
                <span class="panel-icon handle is-moveable">
                    <i
                        class="fa-solid fa-bars"
                        aria-hidden="true"
                    />
                </span>
                <span
                    v-if="space?.children && space.children.length > 0"
                    class="panel-icon"
                >
                    <i
                        class="fa-solid fa-caret-down"
                        aria-hidden="true"
                    />
                </span>

                {{ space.name }}
            </div>

            <div class="level-right">
                <biz-button-link
                    v-if="canAddItem"
                    class="is-white"
                    :href="route('admin.spaces.create', {parent: space.id})"
                >
                    <biz-icon
                        class="is-small"
                        :icon="icon.add"
                    />
                </biz-button-link>

                <biz-button-link
                    class="is-white"
                    :href="route('admin.spaces.edit', space.id)"
                >
                    <biz-icon
                        class="is-small"
                        :icon="icon.edit"
                    />
                </biz-button-link>

                <biz-button-icon
                    class="is-white ml-1"
                    type="button"
                    :icon="icon.remove"
                    @click="$emit('delete-row', space)"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizIcon from '@/Biz/Icon';
    import icon from '@/Libs/icon-class';

    export default {
        name: 'SpaceItem',

        components: {
            BizButtonIcon,
            BizButtonLink,
            BizIcon,
        },

        props:{
            space: { type: Object, required: true },
            canAddItem: { type: Boolean, required: true },
        },

        emits: [
            'delete-row',
        ],

        data() {
            return {
                icon,
            };
        },
    };
</script>
