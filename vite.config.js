import { defineConfig, loadEnv } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig((command, mode) => {
    let input = [
        'resources/js/app.js',
        'resources/js/bulma-misc.js',
        'resources/js/fontawesome.js',
        'resources/sass/app.sass',
        'themes/biz/js/donation.js',
        'themes/biz/js/page-space.js',
        'themes/biz/js/page.js',
        'themes/biz/js/post.js',
        'themes/biz/js/print-qr-code.js',
        'themes/biz/js/profile-performer.js',
        'themes/biz/js/basic.js',
    ];

    const env = loadEnv(mode, process.cwd(), '');
    const hostUrl = new URL(`${env.APP_URL}`);

    input.push(`themes/${env.THEME_ACTIVE}/js/app.js`);
    input.push(`themes/${env.THEME_ACTIVE}/sass/app.sass`);

    let appRefreshPaths = [
        ...refreshPaths,
        ...new Set([
            `themes/biz/**`,
            `themes/${env.THEME_ACTIVE}/**`,
        ]),
    ];

    return {
        plugins: [
            laravel({
                input,
                refresh: appRefreshPaths,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            VitePWA({ registerType: 'autoUpdate' })
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
        build: {
            target: 'esnext',
            manifest: true,
            outDir: 'public/build',
            rollupOptions: {
                input: input, // Ensure input is properly resolved
            },
        },
    };
});
