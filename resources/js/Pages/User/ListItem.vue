<template>
    <tr>
        <th>{{ user.id }}</th>
        <td>{{ user.full_name }}</td>
        <td>{{ user.email }}</td>
        <td>{{ roleName }}</td>
        <td>
            <slot name="actions"></slot>
        </td>
    </tr>
</template>

<script>
    import { isEmpty } from 'lodash';

    export default {
        name: 'UserListItem',
        props: {
            user: {type: Object, required: true},
        },
        computed: {
            roleName() {
                if (isEmpty(this.user.roles)) {
                    return null;
                }
                return this.user.roles[0].name;
            },
        },
    };
</script>
