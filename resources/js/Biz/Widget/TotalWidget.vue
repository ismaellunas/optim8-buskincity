<template>
    <div :class="wrapperClass">
        <div
            class="box py-5"
            :class="boxClasses"
            :style="boxStyles"
        >
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h4 class="title is-4 has-text-white">
                            {{ title }}
                        </h4>
                    </div>
                </div>
                <div class="level-right">
                    <div
                        v-for="(total, totalIndex) in totals"
                        :key="totalIndex"
                        class="level-item"
                    >
                        <span
                            v-if="totalIndex > 0"
                            class="is-size-4 has-text-white"
                        > {{ separator }} &nbsp;</span>

                        <biz-button-link
                            class="is-medium is-light"
                            :href="total.url"
                        >
                            <span class="is-size-4 has-text-weight-bold">
                                {{ total.text }}
                            </span>
                        </biz-button-link>
                    </div>

                    <div v-show="totals == null">
                        <biz-button-link
                            class="is-medium is-light"
                            disabled
                            href="#"
                        >
                            <span class="is-size-4 has-text-weight-bold">
                                ...
                            </span>
                        </biz-button-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import { ref } from 'vue';

    export default {
        name: 'TotalWidget',

        components: {
            BizButtonLink,
        },

        props: {
            backgroudColor: {type: String, default: null },
            componentName: { type: String, default: ''},
            grid: { type: [String, Number], default: 6 },
            order: { type: Number, required: true },
            separator: { type: String, default: '/'},
            templateSetting: { type: Object, default: () => {} },
            title: { type: String, default: null},
            url: { type: String, default: null},
            vue: { type: String, default: ''},
        },

        setup() {
            return {
                response: ref(null),
            };
        },

        computed: {
            boxClasses() {
                const classes = [];

                if (!this.backgroudColor) {
                    classes.push('has-background-success');
                }

                return classes;
            },

            boxStyles() {
                const styles = [];

                if (this.backgroudColor) {
                    styles.push('background-color: ' + this.backgroudColor);
                }

                return styles;
            },

            wrapperClass() {
                return ['is-'+this.grid];
            },

            totals() {
                return this.response?.totals;
            },
        },

        mounted() {
            axios.get(this.url)
                .then((response) => {
                    this.response = response.data;
                });
        },
    };
</script>
