import './bootstrap';

// Import modules...
import { appName } from '@/Libs/defaults';
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { LoadingPlugin } from 'vue-loading-overlay';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { LoadingPlugin } from 'vue-loading-overlay';
import VueSweetalert2 from 'vue-sweetalert2';
import VueSocialSharing from 'vue-social-sharing'

window.inertiaEventsCount = {
    navigateCount: 0,
    successCount: 0,
    errorCount: 0,
    isDebug: false,
};

createInertiaApp({
    title: title => `${title} | ${appName}`,
    //resolve: name => require(`./Pages/${name}`),
    progress: {
        color: '#29d',
    },
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
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .mixin({ methods: { route } })
            .use(plugin)
            .use(VueSweetalert2)
            .use(LoadingPlugin, {color: '#3280bf', loader: 'dots', opacity: 0.3, zIndex: 8000})
            .use(VueSocialSharing)
            .mount(el)
    },
})

router.on('success', (event) => {
    window.inertiaEventsCount.successCount++;
    if (window.inertiaEventsCount.isDebug) {
        console.log(`Successfully made a visit to ${event.detail.page.url}`)
    }
});
router.on('error', (errors) => {
    window.inertiaEventsCount.errorCount++;
    if (window.inertiaEventsCount.isDebug) {
        console.log(errors)
    }
});
router.on('navigate', (event) => {
    window.inertiaEventsCount.navigateCount++;
    if (window.inertiaEventsCount.isDebug) {
        console.log(`Navigated to ${event.detail.page.url}`);
    }
});
