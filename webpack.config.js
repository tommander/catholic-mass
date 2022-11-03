const path = require('path');
const webpack = require('webpack');

module.exports = {
  entry: './assets/react/bin/test.js',
  mode: 'none',
  output: {
    filename: 'bundle.js',
    path: path.resolve(__dirname, 'assets/react/dist'),
  },
  plugins: [
    new webpack.DefinePlugin({
      'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV),
      'process.env.BABEL_ENV': JSON.stringify(process.env.BABEL_ENV),      
    })
  ],
};