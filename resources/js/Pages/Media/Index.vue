<template>
<app-layout>
    <template #header>
        Media
    </template>

    <div class="box">
        <sdb-media-library
            :display-view="displayView"
            :query-params="queryParams"
            :records="records"
            :search="search"
            :is-delete-enabled="can.delete"
            :is-download-enabled="can.read"
            :is-edit-enabled="can.edit"
            :is-upload-enabled="can.add"
            @on-media-submitted="onMediaUploadSuccess"
            @on-view-changed="onViewChanged"
        />
    </div>
</app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import SdbMediaLibrary from '@/Sdb/MediaLibrary';
    import { success as successAlert } from '@/Libs/alert';
    import { merge, clone } from 'lodash';
    import { ref } from 'vue';

    export default {
        components: {
            AppLayout,
            SdbMediaLibrary,
        },
        props: {
            can: {},
            records: {},
            pageNumber: String,
            pageQueryParams: Object,
        },
        setup(props) {
            const queryParams = merge(
                {view: 'gallery'},
                props.pageQueryParams
            );

            return {
                queryParams: ref(queryParams),
            };
        },
        data() {
            return {
                displayView: 'gallery',
                loader: null,
            };
        },
        methods: {
            onMediaUploadSuccess(response) {
                successAlert('File has been uploaded');
            },
            onViewChanged(view) {
                this.queryParams['view'] = view;
                const clonedQueryParam = clone(this.queryParams);

                this.$inertia.get(
                    route(
                        'admin.media.index',
                        merge(clonedQueryParam, {page: this.pageNumber})
                    ),
                    {},
                    {
                        replace: true,
                        preserveState: true,
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => this.onEndLoadingOverlay(),
                    }
                );
            },
            search(term) {
                this.queryParams['term'] = term;
                this.$inertia.get(
                    route('admin.media.index', this.queryParams),
                    {},
                    {
                        replace: true,
                        preserveState: true,
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => this.onEndLoadingOverlay(),
                    }
                );
            },
            onStartLoadingOverlay() {
                this.loader = this.$loading.show();
            },
            onEndLoadingOverlay() {
                this.loader.hide();
            },
        },
    }
</script>
