<template>
    <draggable
        class="dragArea"
        tag="ul"
        :list="items"
        group="question"
        item-key="title"
        :animation="300"
        :disabled="!isEditMode"
    >
        <template #item="{ element }">
            <li class="question">
                <p class="has-text-weight-bold">
                    {{ element.question }}
                </p>
                <p>
                    {{ element.answer }}
                </p>
                <nested-question
                    v-if="element.answer == null || element.answer == ''"
                    :items="element.childs"
                />
            </li>
        </template>
    </draggable>
</template>

<script>
    import draggable from "vuedraggable";

    export default {
        name: "nested-question",

        components: {
            draggable,
        },

        props: {
            items: {
                required: true,
                type: Array
            },
            isEditMode: Boolean,
        },
    }
</script>

<style scoped>
    .question {
        width: 100%;
        height: auto;
        border-bottom: 1px solid #ccc;
        padding: 10px 0 10px 0;
    }

    .dragArea {
        padding: 2px 0px 2px 20px;
    }
</style>