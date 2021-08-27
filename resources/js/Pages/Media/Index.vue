<template>
<app-layout>
    <template #header>
        Media
    </template>

    <div class="box">
        <sdb-media-library
            :records="records"
            @on-media-submitted="onMediaUploadSuccess"
        />
    </div>
</app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import SdbMediaLibrary from '@/Sdb/MediaLibrary';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            SdbMediaLibrary,
        },
        props: {
            records: {},
        },
        methods: {
            onMediaUploadSuccess(response) {
                successAlert('File has been uploaded');
            },
        },
    }
</script>
