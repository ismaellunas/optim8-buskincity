require('./bootstrap');
require('../../node_modules/@fortawesome/fontawesome-free/js/all');

// Import modules...
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { InertiaProgress } from '@inertiajs/progress';
import CKEditor from '@ckeditor/ckeditor5-vue';

createInertiaApp({
    title: title => `${title} - My App`,
    //resolve: (name) => import(`./Pages/${name}`),
    resolve: name => require(`./Pages/${name}`),
    setup({ el, app, props, plugin }) {
        createApp({ render: () => h(app, props) })
            .mixin({ methods: { route } })
            .use(plugin)
            .use(CKEditor)
            .mount(el)
    },
})

InertiaProgress.init({ color: '#4B5563' });
