<template>
    <div
        class="column"
        :class="columnClasses"
    >
        <h2 class="title is-4">
            {{ title }}
        </h2>
        <div class="box is-shadowless">
            <p>
                {{ i18n.description }}
            </p>

            <div class="buttons are-small mt-5">
                <a
                    :href="url"
                    class="button is-primary"
                >
                    <span class="icon is-small">
                        <i class="fa-solid fa-arrow-up-right-from-square" />
                    </span>
                    <span class="has-text-weight-bold">
                        {{ i18n.button_url }}
                    </span>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinWidget from '@/Mixins/Widget';

    export default {
        name: 'DefaultWidget',

        mixins: [
            MixinWidget,
        ],

        props: {
            data: {type: Object, required: true},
            title: {type: String, default: ""},
        },

        computed: {
            url() {
                return this.data.setting.url;
            },
        },
    };
</script>
