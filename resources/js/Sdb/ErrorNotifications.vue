<template>
    <div
        v-if="isVisible && hasError"
        class="notification is-danger"
    >
        <button
            class="delete"
            @click.prevent="isVisible = false"
        />
        <ul class="alert alert-danger">
            <template v-for="(error, bag) in errors">
                <template v-if="canDisplayBag(bag)">
                    <li
                        v-for="(message, index) in error"
                        :key="index"
                    >
                        {{ message[0] }}
                    </li>
                </template>
            </template>
        </ul>
    </div>
</template>

<script>
    export default {
        props: {
            errors: {
                type: Object,
                default: () => {},
            },
            bags: {
                type: [Array],
                default: () => [],
            }
        },

        data() {
            return {
                isVisible: false,
            };
        },

        computed: {
            hasError() {
                const self = this;

                let hasError = (
                    this.errors
                    && Object.keys(this.errors).length > 0
                );

                if (hasError && this.bags.length > 0) {
                    hasError = Object.keys(this.errors).find((bag) => {
                        return self.bags.includes(bag);
                    });
                }

                return hasError;
            },
        },

        watch: {
            errors: {
                deep: true,
                handler() {
                    this.isVisible = true;
                }
            }
        },

        mounted() {
            if (this.hasError) {
                this.isVisible = true;
            }
        },

        methods: {
            canDisplayBag(bag) {
                if (this.bags) {
                    return this.bags.includes(bag);
                }
                return true;
            },
        },
    };
</script>
