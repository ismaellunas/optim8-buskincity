<template>
    <div
        class="column"
        :class="wrapperClass"
    >
        <h2 class="title is-4 mt-5">
            {{ title }}
        </h2>
        <div class="box is-shadowless">
            <p>Coming soon! We are brewing a new feature that will allow you to follow your favourite street performers. Have questions?</p>

            <div class="buttons are-small mt-5">
                <a
                    href="#"
                    class="button"
                    target="_blank"
                >
                    <span class="icon is-small">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </span>
                    <span class="has-text-weight-bold">Get in Touch</span>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'StreetPerformersYouMightLikeWidget',

        props: {
            columns: {type: [Number, String], default: 6},
            data: {type: Object, required: true},
            order: {type: Number, required: true},
            title: {type: String, default: ""},
        },

        computed: {
            wrapperClass() {
                return [
                    'is-'+this.columns,
                ];
            },
        },
    };
</script>
