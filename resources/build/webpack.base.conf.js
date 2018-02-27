const { resolve, join } = require('path')
const webpack = require('webpack')
const ExtractTextPlugin = require('extract-text-webpack-plugin')

const appEntry = './resources/src/main.js'
const distPath = resolve(__dirname, '../../webroot/dist')

module.exports = {
  devtool: '#eval-source-map',
  entry: {
    app: appEntry,
    vendor: ['jquery', 'moment', 'fullcalendar', 'daterangepicker', 'vue']
  },
  output: {
    path: distPath,
    filename: '[name].js'
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          extractCSS: true
        }
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: /node_modules/
      },
      {
        test: /\.(png|jpg|gif|svg)$/,
        loader: 'file-loader',
        options: {
          name: '[name].[ext]?[hash]'
        }
      }
    ]
  },
  resolve: {
    extensions: ['.js', '.vue', '.json'],
    alias: {
      'vue$': 'vue/dist/vue.esm.js',
      '@': resolve('./resources/src'),
      jquery: resolve(join(__dirname, '../..', 'node_modules', 'jquery')),
      fullcalendar: 'fullcalendar/dist/fullcalendar'
    }
  },
  plugins: [
    new ExtractTextPlugin('style.css')
  ]
}
