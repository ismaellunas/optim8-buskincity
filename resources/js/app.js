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
    resolve: (name) => import(`./Pages/${name}`),
    //resolve: name => require(`./Pages/${name}`),
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
