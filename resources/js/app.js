require('./bootstrap');

// Import modules...
import { appName } from '@/Libs/defaults';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { InertiaProgress } from '@inertiajs/progress';
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
                return import(`@mod/Space/Resources/assets/js/Pages/${module[1]}`);
            }

            if (module[0] == 'Booking') {
                return import(`@mod/Booking/Resources/assets/js/Pages/${module[1]}`);
            }

            if (module[0] == 'FormBuilder') {
                return import(`@mod/FormBuilder/Resources/assets/js/Pages/${module[1]}`);
            }
        }

        return import(`./Pages/${name}`);
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
