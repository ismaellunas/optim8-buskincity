<template>
    <div>
        <Head :title="title" />

        <section class="hero is-fullheight">
            <div class="hero-body">
                <div class="container has-text-centered">
                    <div class="columns">
                        <div class="column is-three-fifths has-text-left">
                            <div class="level">
                                <div class="level-left">
                                    <div class="level-item">
                                        <slot name="back" />
                                    </div>
                                </div>
                            </div>
                            <section class="section">
                                <div class="columns">
                                    <div class="column is-9 is-offset-1">
                                        <slot />
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="column is-two-fifths has-text-left">
                            <div class="card">
                                <div class="card-image">
                                    <figure class="image is-3by4">
                                        <img src="https://dummyimage.com/550x715/e5e5e5/ffffff.jpg">
                                    </figure>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import LayoutBackendBlank from '@/Layouts/BackendBlank';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        name: 'LayoutAdmin',

        components: {
            Head,
        },

        layout: LayoutBackendBlank,

        props: {
            title: { type: String, required: true },
        },
    }
</script>
