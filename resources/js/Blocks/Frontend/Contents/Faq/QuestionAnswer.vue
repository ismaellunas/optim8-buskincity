<template>
    <div class="column p-0">
        <article
            v-for="(item, index) in items"
            :key="index"
            class="media"
        >
            <div class="media-left">
                <i class="fas fa-question-circle" />
            </div>
            <div class="media-content">
                <div class="content has-text-weight-bold">
                    <p v-html="item.question" />
                </div>

                <article class="media">
                    <div
                        class="content"
                        v-html="item.answer"
                    />
                </article>
            </div>
        </article>
    </div>
</template>

<script>
    export default {
        name: 'FaqQuestionAnswer',

        props: {
            items: {
                type: Array,
                default:() => [],
            },
        },
    }
</script>