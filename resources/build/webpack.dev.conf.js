'use strict'
const { resolve, join } = require('path')
const webpack = require('webpack')
const merge = require('webpack-merge')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const baseWebpackConfig = require('./webpack.base.conf')

const rootDir = resolve(__dirname, '../test/unit')
const buildPath = resolve(rootDir, 'dist')

baseWebpackConfig.plugins = []

delete baseWebpackConfig.entry

module.exports = merge(baseWebpackConfig, {
  devtool: '#eval-source-map',
  entry: {
    tests: resolve(rootDir, 'visual.js')
  },
  output: {
    path: buildPath
  },
  plugins: [
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
