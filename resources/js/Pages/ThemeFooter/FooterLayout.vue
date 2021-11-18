<template>
    <div>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>Footer Layout</b><br>
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column is-6">
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
                <p
                    class="has-text-weight-semibold mt-2"
                    :class="modelValue == 1 ? 'has-text-link' : ''"
                >
                    Standard
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'FooterLayout',

        props: {
            modelValue: {
                type: Number,
                required: true,
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
        border: 2px solid hsl(217, 71%, 53%);
    }

    .card-hover {
        cursor: pointer;
    }
</style>