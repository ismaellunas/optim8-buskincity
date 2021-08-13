<template>
<app-layout>
    <template #header>
        Media
    </template>

    <div class="box">
        <sdb-media-library
            :records="records"
            :upload-route="route('admin.media.upload-image')"
            @on-media-upload-success="onMediaUploadSuccess"
        >
            <template v-slot:actions="slotProps">
                <!--
                <sdb-link class="card-footer-item p-2" @click.prevent="editRecord(slotProps.media)">Edit</sdb-link>
                -->
                <sdb-link class="card-footer-item p-2" @click.prevent="deleteRecord(slotProps.media)">Delete</sdb-link>
            </template>
        </sdb-media-library>
    </div>
</app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbLink from '@/Sdb/Link';
    import SdbMediaLibrary from '@/Sdb/MediaLibrary';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonLink,
            SdbLink,
            SdbMediaLibrary,
        },
        props: {
            records: {},
            baseRouteName: {},
            files: {default: []},
        },
        methods: {
            getEditRoute(id) {
                // return this.baseRouteName.replace('.', '/')+'/'+id+'/edit';
                return route(this.baseRouteName+'.edit', {id});
            },
            deleteRecord(record) {
                if (!confirm('Are you sure?')) return;
                this.$inertia.delete(route(this.baseRouteName+'.destroy', {id: record.id}));
            },
            editRecord(media) {
                console.log('mediaId: ' + media.id);
            },
            onMediaUploadSuccess(response) {
                this.$inertia.reload();
            },
        },
    }
</script>
