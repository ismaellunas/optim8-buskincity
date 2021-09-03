<template>
<app-layout>
    <template #header>
        Media
    </template>

    <div class="box">
        <sdb-media-library
            :records="records"
            :search="search"
            :display-view="displayView"
            :query-params="queryParams"
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
                    }
                );
            }
        },
    }
</script>
