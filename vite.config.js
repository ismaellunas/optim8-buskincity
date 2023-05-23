import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig((command, mode) => {
    let input = [
        'resources/js/app.js',
        'resources/js/frontend.js',
        'resources/js/bulma-misc.js',
        'resources/js/fontawesome.js',
        'resources/sass/app.sass',
        'resources/sass/local.sass',
    ];

    const env = loadEnv(mode, process.cwd(), '');
    const hostUrl = new URL(`${env.APP_URL}`);

    input.push(`themes/${env.THEME_ACTIVE}/js/app.js`);
    input.push(`themes/${env.THEME_ACTIVE}/sass/app.sass`);

    return {
        plugins: [
            laravel({
                input,
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            VitePWA(),
        ],
        resolve: {
            alias: {
                'vue': 'vue/dist/vue.esm-bundler.js',
                '@sass': '/resources/sass',
                '@mod': '/modules',
                '@booking': '/modules/Booking/Resources/assets/js',
                '@formbuilder': '/modules/FormBuilder/Resources/assets/js',
            },
        },
        server: {
            hmr: {
                host: hostUrl.hostname,
            },
        },
    };
});
