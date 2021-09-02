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
            @on-media-submitted="$emit('on-media-submitted', $event)"
        >
            <template v-slot:actions="slotProps">
                <sdb-button-icon
                    class="card-footer-item p-2 is-borderless is-shadowless is-inverted"
                    title="Select"
                    type="button"
                    icon="fas fa-check"
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
        ],
        props: {
            data: {},
            title: {type: String, default: 'Images'},
            search: Function,
        },
        data() {
            return {
                acceptedTypes: acceptedImageTypes,
            };
        },
    }
</script>
