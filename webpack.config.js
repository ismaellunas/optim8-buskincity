const path = require('path');
const webpack = require('webpack');

module.exports = {
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
        },
    },
    /*
    plugins: [
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: true, // default: true
            __VUE_PROD_DEVTOOLS__: false, // default: false
        }),
    ],
    */
};
