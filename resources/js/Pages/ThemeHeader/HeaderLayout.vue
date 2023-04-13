<template>
    <div>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>
                        {{ i18n.header_layout }}
                    </b>
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
            <div class="column is-3">
                <div
                    class="card card-hover"
                    :class="modelValue == 2 ? 'is-active' : ''"
                    @click="updateFormLayout(2)"
                >
                    <div class="card-content">
                        <div class="content">
                            {{ i18n.layout + ' 2' }}
                        </div>
                    </div>
                </div>
                <p
                    class="has-text-weight-semibold mt-2"
                    :class="modelValue == 2 ? 'has-text-link' : ''"
                >
                    {{ i18n.centered_logo }}
                </p>
            </div>
            <div class="column is-3">
                <div
                    class="card card-hover"
                    :class="modelValue == 3 ? 'is-active' : ''"
                    @click="updateFormLayout(3)"
                >
                    <div class="card-content">
                        <div class="content">
                            {{ i18n.layout + ' 3' }}
                        </div>
                    </div>
                </div>
                <p
                    class="has-text-weight-semibold mt-2"
                    :class="modelValue == 3 ? 'has-text-link' : ''"
                >
                    {{ i18n.standard_with_social_media }}
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    import { usePage } from '@inertiajs/vue3';

    export default {
        name: 'HeaderLayout',

        inject: {
            i18n: { default: () => ({
                header_layout : 'Header layout',
                standard : 'Standard',
                centered_logo : 'Centered logo',
                standard_with_social_media : 'Standard with social media',
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