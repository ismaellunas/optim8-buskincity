<template>
    <tr>
        <td v-if="hasCheckbox">
            <slot name="checkbox" />
        </td>
        <td>
            {{ user.full_name }}<br>
            <biz-tag
                v-if="user.is_suspended"
                class="tag is-warning"
            >
                Suspended
            </biz-tag>
        </td>
        <td>{{ user.email }}</td>
        <td>{{ roleName }}</td>
        <td v-if="hasActions">
            <slot name="actions" />
        </td>
    </tr>
</template>

<script>
    import BizTag from '@/Biz/Tag.vue';
    import { isEmpty } from 'lodash';

    export default {
        name: 'UserListItem',

        components: {
            BizTag,
        },

        props: {
            user: { type: Object, required: true },
        },

        computed: {
            hasActions() {
                return !!this.$slots.actions;
            },

            hasCheckbox() {
                return !!this.$slots.checkbox;
            },

            roleName() {
                if (isEmpty(this.user.roles)) {
                    return null;
                }

                return this.user.roles[0].name;
            },
        },
    };
</script>
