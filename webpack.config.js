const path = require('path');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  entry: './assets/app.js',
  output: {
    filename: 'bundle.[contenthash].js',
    path: path.resolve(__dirname, 'public/assets'),
    publicPath: '/assets/',
    clean: true,
  },
  resolve: {
    extensions: ['.js', '.jsx'],
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        type: 'javascript/auto',
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              ['@babel/preset-env', { modules: false }],
              ['@babel/preset-react', { runtime: 'automatic' }]
            ]
          }
        }
      },
      {
        test: /\.scss$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
      }
    ]
  },
  plugins: [
    new MiniCssExtractPlugin({ filename: 'app.[contenthash].css' }),
    new WebpackManifestPlugin({
      fileName: 'manifest.json',
      publicPath: '/assets/',
      writeToFileEmit: true,
      generate: (seed, files) => {
        const out = { files: {} };
        for (const f of files) out.files[f.name] = f.path;
        return out;
      }
    })
  ],
  mode: 'development',
  target: ['web', 'es2020']
};