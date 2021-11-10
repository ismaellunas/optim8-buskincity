<template>
    <div>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>Header Layout</b><br>
                    Last Saved: {{ setting.updated_at }}
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column is-3">
                <div
                    class="card card-hover"
                    :class="modelValue == 1 ? 'is-active' : ''"
                    @click="updateFormLayout(1)"
                >
                    <div class="card-content">
                        <div class="content">
                            Layout 1
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div
                    class="card card-hover"
                    :class="modelValue == 2 ? 'is-active' : ''"
                    @click="updateFormLayout(2)"
                >
                    <div class="card-content">
                        <div class="content">
                            Layout 2
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div
                    class="card card-hover"
                    :class="modelValue == 3 ? 'is-active' : ''"
                    @click="updateFormLayout(3)"
                >
                    <div class="card-content">
                        <div class="content">
                            Layout 3
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'HeaderLayout',

        props: {
            modelValue: {
                type: Number,
                required: true,
            },
            setting: {
                type: Object,
                required: true
            },
        },

        emits: [
            'update:modelValue'
        ],

        setup() {
            return {
                baseRouteName: usePage().props.value.baseRouteName ?? null,
            };
        },

        methods: {
            updateFormLayout(value) {
                this.$emit('update:modelValue', value);
            },
        },
    }
</script>

<style scoped>
    .is-active {
        border: 2px solid rgb(67, 67, 235);
    }

    .card-hover {
        cursor: pointer;
    }
</style>