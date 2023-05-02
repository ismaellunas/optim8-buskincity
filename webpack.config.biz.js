const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");

module.exports = (env) => {
  const theme = env.theme;

  return {
    mode: 'production',
    entry: {
        "app": './themes/'+theme+'/sass/theme_app.sass',
        "app_backend": './resources/sass/theme_app.sass',
        "app_email": './resources/sass/email_app.sass',
    },
    output: {
      path: path.resolve(__dirname, 'storage/theme/css'),
      filename: '[name].sass.bundle.js',
    },
    resolve: {
        alias: {
          '@sass': path.resolve(__dirname, 'resources/sass'),
          '@mod': path.join(__dirname, 'modules'),
        }
    },
    module: {
      rules: [
        {
          test: /\.s[ac]ss$/i,
          use: [
            MiniCssExtractPlugin.loader,
            "css-loader",
            {
              loader: "sass-loader",
              options: {
                implementation: require("sass"),
              },
            },
          ],
        },
      ],
    },
    optimization: {
      minimizer: [
        new CssMinimizerPlugin(),
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: "[name].css",
        chunkFilename: "[id].css",
      })
    ]
  }
};
