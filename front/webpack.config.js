const path = require('path');

module.exports = {
  mode: 'development',
  entry: path.resolve(__dirname, 'index.web.js'),
  output: {
    path: path.resolve(__dirname, 'web-build'),
    filename: 'bundle.js',
  },
  resolve: {
    extensions: ['.web.js', '.js', '.jsx', '.web.tsx', '.tsx', '.ts'],
    alias: {
      'react-native$': 'react-native-web',
    },
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx|tsx|ts)$/,
        exclude: /node_modules/,
        use: 'babel-loader',
      },
      {
        test: /\.(png|jpe?g|gif|svg)$/,
        type: 'asset/resource',
      },
    ],
  },
  devServer: {
    static: path.resolve(__dirname, 'web-build'),
    port: 3000,
    open: false,
  },
  plugins: [
    new (require('html-webpack-plugin'))({
      template: path.resolve(__dirname, 'public/index.html'),
    }),
  ],
};