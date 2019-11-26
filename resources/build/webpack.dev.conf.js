const merge = require('webpack-merge')
const chokidar = require('chokidar')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const base = require('./webpack.base.conf.js')

module.exports = merge(base, {
  mode: 'development',
  devtool: '#eval-source-map',
  entry: './resources/dev/dev.js',
  plugins: [
      new HtmlWebpackPlugin({
        template: './resources/dev/index.html',
        inject: true,
      }),
  ],
  optimization: {
   noEmitOnErrors: true,
  },
  devServer: {
    hot: true,
    hotOnly: true,
    open: true,
    inline: true,
    stats: {
      children: false,
      modules: false,
      chunks: false,
    },
    port: 8080,
    before (app, server) {
      chokidar.watch([
        './**/*.html',
      ]).on('all', function () {
        server.sockWrite(server.sockets, 'content-changed');
      })
    },
  }
})
