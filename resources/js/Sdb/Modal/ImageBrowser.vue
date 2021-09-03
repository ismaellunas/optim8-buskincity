<template>
    <sdb-modal-card
        :content-class="['is-huge']"
        :is-close-hidden="true"
        @close="$emit('close')"
    >
        <template v-slot:header>
            <p class="modal-card-title">{{ title }}</p>
            <sdb-button
                @click="$emit('close')"
                class="delete is-primary"
                type="button"
                aria-label="close"
            />
        </template>

        <template v-slot:footer>
            <sdb-pagination
                :is-ajax="true"
                :links="data?.links ?? []"
                @on-clicked-pagination="$emit('on-clicked-pagination', $event)"
            />
        </template>

        <sdb-media-library
            :is-ajax="true"
            :is-delete-enabled="false"
            :is-edit-enabled="false"
            :is-pagination-displayed="false"
            :records="data"
            :accepted-types="acceptedTypes"
            :search="search"
            :query-params="queryParams"
            @on-media-submitted="$emit('on-media-submitted', $event)"
            @on-view-changed="$emit('on-view-changed', $event)"
        >
            <template v-slot:actions="slotProps">
                <sdb-button-icon
                    icon="fas fa-check"
                    title="Select"
                    type="button"
                    :class="{'is-borderless is-shadowless is-inverted is-primary': true, 'card-footer-item  p-2': queryParams.view !== 'list'}"
                    @click="$emit('on-media-selected', slotProps.media, $event)"
                >
                    <span>Select</span>
                </sdb-button-icon>
            </template>
        </sdb-media-library>
    </sdb-modal-card>
</template>

<script>
    import SdbButton from '@/Sdb/Button';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbMediaLibrary from '@/Sdb/MediaLibrary';
    import SdbModalCard from '@/Sdb/ModalCard';
    import SdbPagination from '@/Sdb/Pagination';
    import { acceptedImageTypes } from '@/Libs/defaults';

    export default {
        components: {
            SdbButton,
            SdbButtonIcon,
            SdbMediaLibrary,
            SdbModalCard,
            SdbPagination,
        },
        emits: [
            'close',
            'on-clicked-pagination',
            'on-media-selected',
            'on-media-submitted',
            'on-view-changed',
        ],
        props: {
            data: {},
            queryParams: Object,
            search: Function,
            title: {type: String, default: 'Images'},
        },
        data() {
            return {
                acceptedTypes: acceptedImageTypes,
            };
        },
    }
</script>
