var path    = require('path');
var pkg     = require('../package.json');
var webpack = require('webpack');
var loaders = require('./loaders');
var plugins = require('./plugins');

var config = {
  context: path.join(__dirname, '../app'),
  entry: './app.jsx',
  output: { 
  	path: path.join(__dirname, '../app'),
  	filename: 'bundle.js'
  },
  module: {
    loaders: loaders
  },
  devServer: {
    contentBase: path.join(__dirname, '../app'),
    hot: true,
    noInfo: false,
    inline: true,
    stats: { colors: true }
  },
  debug: true
};

module.exports = config;
