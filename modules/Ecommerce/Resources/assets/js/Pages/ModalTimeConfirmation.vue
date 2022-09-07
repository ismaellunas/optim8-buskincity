<template>
    <biz-modal-card
        @close="close()"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ title }}
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="close()"
            />
        </template>

        <form @submit.prevent="reschedule">
            <h5 class="title is-5">
                {{ product.name }}
            </h5>

            <p>
                <span class="tag">
                    {{ product.identifier }}
                </span>
            </p>

            <table class="table">
                <tr
                    v-for="(detail, index) in details"
                    :key="index"
                >
                    <th>{{ detail.field }}</th>
                    <td>{{ detail.value }}</td>
                </tr>
            </table>
        </form>

        <template #footer>
            <div
                class="columns mx-0"
                style="width: 100%"
            >
                <div class="column px-0">
                    <div class="is-pulled-right">
                        <biz-button @click="close()">
                            Cancel
                        </biz-button>

                        <slot name="actions" />
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizModalCard from '@/Biz/ModalCard';

    export default {
        components: {
            BizButton,
            BizModalCard,
        },

        props: {
            details: { type: Array, required: true },
            product: { type: Object, required: true },
            submitText: { type: String, default: "Book" },
            title: { type: String, default: "Reschedule Event Confirmation" },
        },

        emits: [
            'close',
        ],

        methods: {
            close() {
                this.$emit('close');
            },
        },
    };
</script>
