<template>
    <div>
        <div class="box">
            <biz-media-library
                :allow-multiple="true"
                :max-files="5"
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
                :instructions="instructions.mediaLibrary"
                @on-view-changed="onViewChanged"
                @on-type-changed="onTypeChanged"
            />
        </div>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizMediaLibrary from '@/Biz/MediaLibrary.vue';
    import { merge, clone } from 'lodash';
    import { ref } from 'vue';

    export default {
        name: 'MediaIndex',

        components: {
            BizMediaLibrary,
        },

        mixins: [
            MixinFilterDataHandle,
        ],

        provide() {
            return {
                i18n: this.i18n,
            }
        },

        layout: AppLayout,

        props: {
            acceptedTypes: { type: Array, required: true },
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            pageNumber: { type: String, default: null },
            pageQueryParams: { type: Object, default: () => {} },
            records: { type: Object, required: true },
            title: { type: String, required: true },
            instructions: { type: Object, default: () => {} },
            i18n: { type: Object, default: () => {} }
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
