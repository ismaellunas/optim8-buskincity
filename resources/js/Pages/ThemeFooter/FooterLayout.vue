<template>
    <div>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>
                        {{ i18n.footer_layout }}
                    </b>
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
                            {{ i18n.layout + ' 1' }}
                        </div>
                    </div>
                </div>
                <p
                    class="has-text-weight-semibold mt-2"
                    :class="modelValue == 1 ? 'has-text-link' : ''"
                >
                    {{ i18n.standard }}
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    import { usePage } from '@inertiajs/vue3';

    export default {
        name: 'FooterLayout',

        inject: {
            i18n: { default: () => ({
                footer_layout : 'Footer Layout',
                standard : 'Standard',
                layout : 'Layout',
            }) },
        },

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
                baseRouteName: usePage().props.baseRouteName ?? null,
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