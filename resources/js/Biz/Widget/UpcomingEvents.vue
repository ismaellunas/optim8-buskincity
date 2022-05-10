<template>
    <div
        class="column"
        :class="wrapperClass"
    >
        <h2
            class="title is-4"
            :class="{'mt-5': order != 0}"
        >
            {{ title }}
        </h2>

        <div class="box is-shadowless">
            <p>Coming soon! We are brewing a new feature that will allow you to see nearby city events and performances. Have questions?</p>

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
        name: 'UpcomingEventsWidget',

        props: {
            data: {type: Object, required: true},
            title: {type: String, default: ""},
            order: {type: Number, required: true},
            columns: {type: [Number, String], default: 6},
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
