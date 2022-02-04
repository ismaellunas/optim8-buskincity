/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

let theme = process.env.npm_config_theme;

if (theme) {

    require(`${__dirname}/themes/${theme}/webpack.mix.js`);
    
} else {

    const mix = require('laravel-mix');
    const path = require('path');

    mix.js('resources/js/app.js', 'public/js').vue()
        /*
        .postCss('resources/css/app.css', 'public/css', [
            require('postcss-import'),
        ])
        */
        .sass('resources/sass/app.scss', 'public/css')
        .alias({
            '@': path.join(__dirname, 'resources/js')
        });

    mix.js('resources/js/frontend.js', 'public/js').vue()

    if (mix.inProduction()) {

        mix.version();

    } else {

        mix.copy('node_modules/vue-loading-overlay/dist/vue-loading.css', 'public/css');
        // mix.js('resources/js/local.js', 'public/js');

        mix.browserSync({
            host: '127.0.0.1',
            proxy: 'localhost',
            open: false,
            files: [
                'app/**/*.php',
                'resources/views/**/*.php',
                `${Config.publicPath || 'public'}/**/*.(js|css)`,
                'themes/**/*.php',
            ],
        });
    }
}
