<template>
    <biz-modal-card
        :content-class="['is-huge']"
        :is-close-hidden="true"
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title">
                {{ title }}
            </p>
            <biz-button
                aria-label="close"
                class="delete is-primary"
                type="button"
                @click="$emit('close')"
            />
        </template>

        <template #footer>
            <biz-pagination
                :is-ajax="true"
                :links="data?.links ?? []"
                @on-clicked-pagination="$emit('on-clicked-pagination', $event)"
            />
        </template>

        <biz-media-library
            :accepted-types="acceptedTypes"
            :is-ajax="true"
            :is-delete-enabled="false"
            :is-download-enabled="isDownloadEnabled"
            :is-edit-enabled="false"
            :is-pagination-displayed="false"
            :is-upload-enabled="isUploadEnabled"
            :query-params="queryParams"
            :records="data"
            :search="search"
            @on-media-submitted="$emit('on-media-submitted', $event)"
            @on-view-changed="$emit('on-view-changed', $event)"
        >
            <template #actions="slotProps">
                <biz-button-icon
                    icon="fas fa-check"
                    title="Select"
                    type="button"
                    :class="{'is-borderless is-shadowless is-inverted is-primary': true, 'card-footer-item  p-2': queryParams.view !== 'list'}"
                    @click="$emit('on-media-selected', slotProps.media, $event)"
                >
                    <span>Select</span>
                </biz-button-icon>
            </template>
        </biz-media-library>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizMediaLibrary from '@/Biz/MediaLibrary';
    import BizModalCard from '@/Biz/ModalCard';
    import BizPagination from '@/Biz/Pagination';
    import { acceptedImageTypes } from '@/Libs/defaults';

    export default {
        components: {
            BizButton,
            BizButtonIcon,
            BizMediaLibrary,
            BizModalCard,
            BizPagination,
        },
        props: {
            acceptedFileType: {
                type: Array,
                default: null,
            },
            data: {},
            isDownloadEnabled: {type: Boolean, default: true},
            isUploadEnabled: {type: Boolean, default: true},
            queryParams: Object,
            search: Function,
            title: {type: String, default: 'Images'},
        },
        emits: [
            'close',
            'on-clicked-pagination',
            'on-media-selected',
            'on-media-submitted',
            'on-view-changed',
        ],
        setup(props) {
            return {
                acceptedTypes: props.acceptedFileType ?? acceptedImageTypes,
            };
        },
    }
</script>
