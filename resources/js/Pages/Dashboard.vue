<template>
    <layout>
        <template #header>
            <h1 class="title is-2">
                {{ title }}
            </h1>
        </template>

        <!--
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <biz-widget-columns :widgets="widgets" />
                </div>
            </div>
        </div>
        -->

        <div class="section is-small has-background-light">
            <div class="container">
                <div class="columns is-multiline">
                    <div class="column is-6">
                        <h2 class="title is-4">Become a Performer</h2>
                        <div class="box is-shadowless">
                            <p>Apply to become a performer on BuskinCity. It will allow you to have your page, receive donations, private bookings, and build a bigger audience.</p>

                            <div class="buttons are-small mt-5">
                                <a href="#" class="button is-primary" target="_blank">
                                    <span class="icon is-small">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </span>
                                    <span class="has-text-weight-bold">Apply Now</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="column is-6">
                        <h2 class="title is-4">Upcoming Events</h2>
                        <div class="box is-shadowless">
                            <p>Coming soon! We are brewing a new feature that will allow you to see nearby city events and performances. Have questions?</p>

                            <div class="buttons are-small mt-5">
                                <a href="#" class="button" target="_blank">
                                    <span class="icon is-small">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </span>
                                    <span class="has-text-weight-bold">Get in Touch</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="column is-12">
                        <h2 class="title is-4 mt-5">Street Performers You Might Like</h2>
                        <div class="box is-shadowless">
                            <p>Coming soon! We are brewing a new feature that will allow you to follow your favourite street performers. Have questions?</p>

                            <div class="buttons are-small mt-5">
                                <a href="#" class="button" target="_blank">
                                    <span class="icon is-small">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </span>
                                    <span class="has-text-weight-bold">Get in Touch</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="column is-12">
                        <h2 class="title is-4 mt-5">Want to Become a Street Performer?</h2>
                        <div class="box is-shadowless">
                            <p>Coming soon! We are brewing a new feature that will allow you to have access to educational materials to help you become a successful screet performer. Have questions?</p>

                            <div class="buttons are-small mt-5">
                                <a href="#" class="button" target="_blank">
                                    <span class="icon is-small">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </span>
                                    <span class="has-text-weight-bold">Get in Touch</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </layout>
</template>

<script>
    import BizWidgetColumns from '@/Biz/Widget/WidgetColumns';
    import Layout from '@/Layouts/User';

    export default {
        components: {
            BizWidgetColumns,
            Layout,
        },

        props: {
            title: { type: String, default: 'Dashboard' },
            widgets: { type: Array, default:() => [] },
        },
    };
</script>
