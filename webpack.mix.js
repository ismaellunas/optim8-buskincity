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
    .copy('node_modules/sweetalert2/dist/sweetalert2.min.css', 'public/css')
    .webpackConfig(require('./webpack.config'));

if (mix.inProduction()) {
    mix.version();
} else {
    mix.copy('node_modules/vue-loading-overlay/dist/vue-loading.css', 'public/css');
    mix.js('resources/js/local.js', 'public/js');
    mix.js('resources/js/frontend-app.js', 'public/js');

    mix.after(() => {
        exec('php artisan optimize:clear', (error, stdout, stderr) => {
            if (error) {
                console.error(`exec error: ${error}`);
                return;
            }
            console.log(`stdout: ${stdout}`);
            console.error(`stderr: ${stderr}`);
        });
    });

    mix.browserSync({
        proxy: 'http://localhost:8000'
    });
}
