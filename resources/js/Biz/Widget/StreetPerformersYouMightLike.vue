<template>
    <div class="column is-12">
        <h2
            class="title is-4"
            :class="{'mt-5': order != 0}"
        >
            Street Performers You Might Like
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
            data: {type: Object, required: true},
            title: {type: String, default: ""},
            order: {type: Number, required: true},
        },
    };
</script>
