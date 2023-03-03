<template>
    <tr>
        <td>{{ dashedName }}</td>
        <td>{{ ancestorNames }}</td>
        <td>{{ space.typeName }}</td>
        <td>
            <div class="level-right">
                <biz-button-link
                    v-if="can.add && space.depth < 2"
                    class="is-ghost has-text-black"
                    :href="route(routeCreate, {parent: space.id})"
                >
                    <biz-icon
                        class="is-small"
                        :icon="iconAdd"
                    />
                </biz-button-link>

                <biz-button-link
                    class="is-ghost has-text-black"
                    :href="route(routeEdit, space.id)"
                >
                    <biz-icon
                        class="is-small"
                        :icon="iconEdit"
                    />
                </biz-button-link>

                <biz-button-icon
                    v-if="space.can.delete"
                    class="is-ghost has-text-black ml-1"
                    type="button"
                    :icon="iconRemove"
                    @click.prevent="$emit('delete-row', space)"
                />
            </div>
        </td>
    </tr>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import { add as iconAdd, edit as iconEdit, remove as iconRemove } from '@/Libs/icon-class';
    import { repeat } from 'lodash';

    export default {
        name: 'SpaceRow',

        components: {
            BizButtonIcon,
            BizButtonLink,
            BizIcon,
        },

        props: {
            can: { type: Object, required: true },
            routeCreate: { type: String, default: 'admin.spaces.create' },
            routeEdit: { type: String, default: 'admin.spaces.edit' },
            space: { type: Object, required: true },
        },

        emits: [
            'delete-row',
        ],

        setup(props) {
            return {
                iconAdd,
                iconEdit,
                iconRemove,
            };
        },

        computed: {
            ancestorNames() {
                return this.space.ancestorNames.length
                    ? this.space.ancestorNames.join(' ❯ ')
                    : '-';
            },

            dashedName() {
                return repeat('—', this.space.depth) + ' ' + this.space.name;
            },
        },
    };
</script>
