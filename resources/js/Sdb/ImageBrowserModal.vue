<template>
    <sdb-modal-card
        :content-class="['is-huge']"
        :is-close-hidden="false"
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
                :links="data?.links ?? []"
                :is-ajax="true"
                @on-clicked-pagination="onClickedPagination"
            />
        </template>

        <div class="columns is-multiline">
            <div class="column is-3" v-for="image in data?.data">
                <div class="card">
                    <div class="card-image">
                        <figure class="image is-4by3">
                            <img
                                :src="image.file_url"
                                :alt="image.file_name"
                            />
                        </figure>
                    </div>
                    <div class="card-content p-2">
                        <div class="content">
                            <p>{{ image.file_name }}</p>
                        </div>
                    </div>
                    <footer class="card-footer">
                        <sdb-button
                            @click.prevent="$emit('on-selected-image', image)"
                            class="card-footer-item">
                            Select
                        </sdb-button>
                    </footer>
                </div>
            </div>
        </div>
    </sdb-modal-card>
</template>

<script>
    import SdbButton from '@/Sdb/Button';
    import SdbModalCard from '@/Sdb/ModalCard';
    import SdbPagination from '@/Sdb/Pagination';
    import { isBlank } from '@/Libs/utils';

    export default {
        components: {
            SdbButton,
            SdbModalCard,
            SdbPagination,
        },
        emits: ['close', 'on-clicked-pagination', 'on-selected-image'],
        props: {
            data: {},
            isCloseHidden: {},
            title: {type: String, default: 'Images'},
        },
        methods: {
            onClickedPagination(url) {
                this.$emit('on-clicked-pagination', url);
            }
        },
    }
</script>
