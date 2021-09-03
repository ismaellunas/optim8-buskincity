<template>
    <table class="table is-striped is-hoverable is-fullwidth">
        <thead>
            <tr>
                <th></th>
                <th>File Name</th>
                <th>Date Modified</th>
                <th>Type</th>
                <th>Size</th>
                <th>
                    <div class="level-right">Actions</div>
                </th>
            </tr>
        </thead>
        <tbody>
            <template v-for="medium in media">
                <slot :medium="medium"></slot>
            </template>
        </tbody>
    </table>
</template>

<script>
    export default {
        name: 'MediaList', 
        props: {
            media: {},
        },
    }
</script>
