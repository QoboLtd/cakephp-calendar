const merge = require('webpack-merge')
const base = require('./webpack.base.conf')
const UglifyJsPlugin = require('uglifyjs-webpack-plugin')

module.exports = merge(base, {
  mode: 'production',
  devtool: false,
  optimization: {
    minimizer: [new UglifyJsPlugin()]
  }
})
