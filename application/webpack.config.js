const path = require('path');
const webpack = require('webpack');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');

const NODE_ENV = process.env.NODE_ENV || 'development';

module.exports = {
  mode: NODE_ENV,
  devtool: NODE_ENV === 'development' ? 'source-map' : false,
  entry: {
    web: path.resolve(__dirname, 'resources', 'assets', 'web.js'),
    // admin: path.resolve(__dirname, 'resources', 'assets', 'admin.js')
  },
  output: {
    path: path.resolve(__dirname, '..', 'public_html', 'assets'),
    filename: '[name].js',
    publicPath: '/assets/',
  },
  optimization: {
    minimizer: [
      new UglifyJsPlugin({ cache: true, parallel: true }),
      new OptimizeCssAssetsPlugin({}),
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].css',
      chunkFilename: '[name].css',
    }),
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
      'window.Jquery': 'jquery',
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
            name: 'images/[name]-[hash:8].[ext]',
          },
        },
      },
      {
        test: /\.svg$/,
        use: {
          loader: 'file-loader',
          options: {
            name: 'svg/[name]-[hash:8].[ext]',
          },
        },
      },
      {
        test: /\.(ttf|eot|woff(2)?)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
        loader: 'file-loader',
        options: {
          name: 'fonts/[name]-[hash:8].[ext]',
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
