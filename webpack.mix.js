const mix = require('laravel-mix');
const { exec } = require('child_process');

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

mix.js('resources/js/app.js', 'public/js').vue()
    /*
    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
    ])
    */
    .sass('resources/sass/app.scss', 'public/css')
    .webpackConfig(require('./webpack.config'));

mix.js('resources/js/frontend.js', 'public/js').vue()

if (mix.inProduction()) {
    mix.version();
} else {
    mix.js('resources/js/local.js', 'public/js')
        .js('resources/js/fontawesome.js', 'public/js')
        .sass('resources/sass/local.scss', 'public/css');

    mix.browserSync({
        host: '127.0.0.1',
        proxy: 'localhost',
        open: false,
    });
}
