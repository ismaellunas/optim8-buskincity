const mix = require("laravel-mix");
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
 
let theme = process.env.npm_config_theme;

mix.setPublicPath("public/themes/"+theme)
    .js(`${__dirname}/js/app.js`, "js")
    .vue()
    .sass(`${__dirname}/sass/app.sass`, "css")
    .alias({
        '@': path.join(__dirname, '../../resources/js'),
        '@sass': path.join(__dirname, '../../resources/sass')
    });

if (mix.inProduction()) {
    mix.version();
}