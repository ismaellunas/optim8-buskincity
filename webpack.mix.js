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
        .sass('resources/sass/app.sass', 'public/css')
        .css('resources/css/template.css', 'public/css')
        .alias({
            '@': path.join(__dirname, 'resources/js')
        });

    mix.js('resources/js/frontend.js', 'public/js').vue()

    if (mix.inProduction()) {

        mix.version();

    } else {

        mix
            .js('resources/js/local.js', 'public/js')
            .js('resources/js/fontawesome.js', 'public/js')
            .sass('resources/sass/local.sass', 'public/css')
            .copy('node_modules/tinymce/skins/ui/oxide/skin.min.css', 'public/js/skins/ui/oxide')
            .copy('node_modules/tinymce/skins/ui/oxide/content.min.css', 'public/js/skins/ui/oxide')
            .copy('node_modules/tinymce/skins/ui/oxide/content.inline.min.css', 'public/js/skins/ui/oxide')
            .copy('node_modules/tinymce/skins/content/default/content.css', 'public/js/skins/content/default');

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
