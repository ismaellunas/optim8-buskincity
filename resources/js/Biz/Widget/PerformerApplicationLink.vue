<template>
    <div
        class="column"
        :class="columnClasses"
    >
        <h2 class="title is-4">
            {{ title }}
        </h2>

        <div class="box is-shadowless">
            <template v-if="! hasSubmitted">
                <p>
                    {{ i18n.description }}
                </p>
                <div class="buttons are-small mt-5">
                    <a
                        class="button is-primary"
                        :href="data.pageUrl"
                    >
                        <span class="icon is-small">
                            <i class="fa-solid fa-arrow-up-right-from-square" />
                        </span>
                        <span class="has-text-weight-bold">
                            {{ i18n.button_apply }}
                        </span>
                    </a>
                </div>
            </template>

            <div
                v-else
                class="notification is-info is-light"
            >
                <p>
                    {{ i18n.under_review }}
                </p>
                <div class="buttons are-small mt-5">
                    <biz-button-link
                        :href="data.pageUrl"
                        class="is-info"
                    >
                        <span class="icon is-small">
                            <i class="fa-solid fa-arrow-up-right-from-square" />
                        </span>
                        <span class="has-text-weight-bold">
                            {{ i18n.button_get_in_touch }}
                        </span>
                    </biz-button-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinWidget from '@/Mixins/Widget';
    import BizButtonLink from '@/Biz/ButtonLink.vue';

    export default {
        name: 'PerformerApplicationLink',

        components: {
            BizButtonLink,
        },

        mixins: [
            MixinWidget,
        ],

        props: {
            data: { type: Object, default: null },
            title: { type: String, default: "" },
        },

        computed: {
            hasSubmitted() {
                return this.data.hasSubmitted;
            },
        },
    };
</script>