'use strict'
const { resolve, join } = require('path')
const webpack = require('webpack')
const merge = require('webpack-merge')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const baseWebpackConfig = require('./webpack.base.conf')
const ExtractTextPlugin = require('extract-text-webpack-plugin')

const buildPath = resolve(__dirname, '../../webroot/dist')

baseWebpackConfig.plugins = []

module.exports = merge(baseWebpackConfig, {
  devtool: 'inline-sourcemap',
  externals: {
    jquery: 'jQuery',
  },
  plugins: [
    new ExtractTextPlugin('style.css'),
    new webpack.optimize.CommonsChunkPlugin({
      names: ['vendor'],
      minChunks: function (module, count) {
        // any required modules inside node_modules are extracted to vendor
        return (
          module.resource &&
          /\.js$/.test(module.resource) && // This seems unnecessary
          module.resource.indexOf(
            join(__dirname, '../node_modules')
          ) === 0
        )
      }
    }),
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: '"development"'
      }
    }),
    new HtmlWebpackPlugin({
      title: 'Testing Qobrix App',
      chunkSortMode: 'dependency'
    })
  ]
})
