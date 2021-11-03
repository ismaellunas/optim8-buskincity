<template>
    <main>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>Menu Navigation</b><br>
                    Last Saved: -
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Nobis iste accusamus cumque deserunt! Labore, placeat temporibus exercitationem quod recusandae perferendis velit suscipit vero, officia magnam ab minus, odit cupiditate porro?</p>
            </div>
        </div>
    </main>
</template>

<script>
    import { success as successAlert  } from '@/Libs/alert';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'FooterNavigation',

        props: {
            //
        },

        setup() {
            return {
                baseRouteName: usePage().props.value.baseRouteName ?? null,
            };
        },

        data() {
            return {
                //
            };
        },

        methods: {
            //
        },
    }
</script>