import './bootstrap';

// Import modules...
import { appName } from '@/Libs/defaults';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import VueSweetalert2 from 'vue-sweetalert2';
import VueLoading from 'vue-loading-overlay';
import VueSocialSharing from 'vue-social-sharing'

createInertiaApp({
    title: title => `${title} | ${appName}`,
    //resolve: name => require(`./Pages/${name}`),
    resolve: (name) => {
        let module = name.split('::');

        if (module[1]) {
            if (module[0] == 'Space') {
                return resolvePageComponent(
                    `../../modules/Space/Resources/assets/js/Pages/${module[1]}.vue`,
                    import.meta.glob(`../../modules/Space/Resources/assets/js/Pages/**/*.vue`)
                );
            }

            if (module[0] == 'Booking') {
                return resolvePageComponent(
                    `../../modules/Booking/Resources/assets/js/Pages/${module[1]}.vue`,
                    import.meta.glob(`../../modules/Booking/Resources/assets/js/Pages/**/*.vue`)
                );
            }

            if (module[0] == 'FormBuilder') {
                return resolvePageComponent(
                    `../../modules/FormBuilder/Resources/assets/js/Pages/${module[1]}.vue`,
                    import.meta.glob(`../../modules/FormBuilder/Resources/assets/js/Pages/**/*.vue`)
                );
            }
        }

        return resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        );
    },
    setup({ el, app, props, plugin }) {
        createApp({ render: () => h(app, props) })
            .mixin({ methods: { route } })
            .use(plugin)
            .use(VueSweetalert2)
            .use(VueLoading, {color: '#3280bf', loader: 'dots', opacity: 0.3, zIndex: 8000})
            .use(VueSocialSharing)
            .mount(el)
    },
})

InertiaProgress.init({ color: '#4B5563' });
