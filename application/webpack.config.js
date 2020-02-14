const path = require('path');
const webpack = require('webpack');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const fs = require('fs').promises;

const NODE_ENV = process.env.NODE_ENV || 'development';
const ASSETS_PATH = path.join(__dirname, 'resources', 'assets');

module.exports = (async function() {
  return (await fs.readdir(path.resolve(ASSETS_PATH, 'components'))).reduce(
    (initial, name) => {
      if (name.startsWith('.')) {
        return initial;
      }

      const newName = name.replace(/.js/g, '');
      const fileIndex = name.endsWith('.js') ? name : `${newName}/index.js`;
      const relativePath = path.resolve(ASSETS_PATH, 'components', fileIndex);

      initial[`components/${newName}`] = relativePath;

      return initial;
    },
    {}
  );
})().then(entryComponents => {
  const outputFilename = ({ chunk: { name } }) => {
    if (['components'].some(v => name.startsWith(v))) {
      return `assets/components/${name.replace('components/', '')}.js`;
    }

    return 'assets/[name]/app.js';
  };

  return {
    mode: NODE_ENV,
    devtool: NODE_ENV === 'development' ? 'source-map' : false,
    entry: {
      web: path.resolve(ASSETS_PATH, 'app.js'),
      ...entryComponents,
    },
    output: {
      path: path.resolve(__dirname, '..', 'public_html'),
      filename: outputFilename,
      publicPath: '/',
    },
    optimization: {
      minimizer: [
        new UglifyJsPlugin({ cache: true, parallel: true }),
        new OptimizeCssAssetsPlugin({}),
      ],
    },
    plugins: [
      new webpack.ProgressPlugin(),
      new MiniCssExtractPlugin({
        filename: 'assets/[name]/app.css',
        chunkFilename: 'assets/[name]/app.css',
      }),
      new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery',
        'window.$': 'jquery',
        'window.jQuery': 'jquery',
      }),
    ],
    module: {
      rules: [
        {
          test: /\.s?[ac]ss$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                importLoaders: 2,
              },
            },
            'sass-loader',
          ],
        },
        {
          test: /\.(png|jpe?g|gif)$/,
          use: {
            loader: 'file-loader',
            options: {
              name: 'static/images/[name]-[hash:8].[ext]',
            },
          },
        },
        {
          test: /\.svg$/,
          use: {
            loader: 'file-loader',
            options: {
              name: 'static/svg/[name]-[hash:8].[ext]',
            },
          },
        },
        {
          test: /\.(ttf|eot|woff(2)?)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
          loader: 'file-loader',
          options: {
            name: 'static/fonts/[name]-[hash:8].[ext]',
          },
        },
        {
          test: /\.m?jsx?$/,
          exclude: /(node_modules|bower_components)/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env', '@babel/preset-react'],
            },
          },
        },
        {
          test: /\.vue$/,
          exclude: /(node_modules|bower_components)/,
          use: 'vue-loader',
        },
      ],
    },
    resolve: {
      extensions: ['.js', '.jsx', '.ts', '.vue', '.css', '.scss', '.sass'],
    },
  };
});
