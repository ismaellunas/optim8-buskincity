<template>
    <app-layout :title="title">
        <template #header>
            {{ title }}
        </template>

        <div class="box">
            <biz-media-library
                :accepted-types="acceptedTypes"
                :display-view="displayView"
                :is-delete-enabled="can.delete"
                :is-download-enabled="can.read"
                :is-edit-enabled="can.edit"
                :is-filter-enabled="true"
                :is-upload-enabled="can.add"
                :query-params="queryParams"
                :records="records"
                :search="search"
                @on-media-submitted="onMediaUploadSuccess"
                @on-view-changed="onViewChanged"
                @on-type-changed="onTypeChanged"
            />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import BizMediaLibrary from '@/Biz/MediaLibrary';
    import { success as successAlert } from '@/Libs/alert';
    import { merge, clone } from 'lodash';
    import { ref } from 'vue';

    export default {
        components: {
            AppLayout,
            BizMediaLibrary,
        },
        mixins: [
            MixinFilterDataHandle,
        ],
        props: {
            acceptedTypes: Array,
            baseRouteName: String,
            can: {},
            pageNumber: String,
            pageQueryParams: Object,
            records: {},
            title: { type: String, required: true },
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
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => this.onEndLoadingOverlay(),
                    }
                );
            },
            onTypeChanged(types) {
                this.queryParams['types'] = types;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            }
        },
    }
</script>
