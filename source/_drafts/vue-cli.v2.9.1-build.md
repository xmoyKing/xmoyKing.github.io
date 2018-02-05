---
title: vue-cli.v2.9.1下Build详解
categories: js
tags:
  - js
date: 2018-02-05 10:17:30
updated: 2018-02-05 10:17:30
---

vue-cli 2.9.1下构建发布时（`npm run build`）的详细流程。

其实vue-cli v2.9有好几种初始化项目的方式，参考[vue-cli v2.9 官方README.md](https://github.com/vuejs/vue-cli/tree/v2.9.0)

初始化项目的命名为：`$ vue init <template-name> <project-name>`
其中`<template-name>`表示采用的脚手架模版，vue-cli内置了几种方式：
- webpack - A full-featured Webpack + vue-loader setup with hot reload, linting, testing & css extraction.
- webpack-simple - A simple Webpack + vue-loader setup for quick prototyping.
- browserify - A full-featured Browserify + vueify setup with hot-reload, linting & unit testing.
- browserify-simple - A simple Browserify + vueify setup for quick prototyping.
- pwa - PWA template for vue-cli based on the webpack template
- simple - The simplest possible Vue setup in a single HTML file


执行`vue init webpack project-name`后的目录结构如下：
```shell
.
├── build/                      # webpack 配置文件 files
│   ├── build.js                # 构建脚本
│   ├── check-versions.js       # 用于检查node、npm以及各种依赖包的版本
│   ├── logo.png                # vue logo
│   ├── utils.js                # 构建脚本时需要用到的一些自定义的小工具，如各种loaders处理函数
│   ├── vue-loader.conf.js      # vue-loader插件配置（用于处理*.vue文件，将这些*.vue文件转换为js模块）
│   ├── webpack.base.conf.js    # 无论是开发环境还是生产环境都需要用到的webpack基本配置，抽取出来
│   ├── webpack.dev.conf.js     # 开发环境下配置
│   └── webpack.prod.conf.js    # 生产环境下配置
├── config/                     # 项目配置目录
│   ├── index.js                # 主要项目配置
│   ├── dev.env.js              # 开发时启用的配置
│   ├── prod.env.js             # 生产时启用的配置
│   └── test.env.js             # 测试时启用的配置
├── ...
├── .babelrc                    # babel配置,用于将ES6转换当前浏览器可解析识别的ES5
├── .postcssrc.js               # postcss配置，处理*.vue文件内的样式
├── package.json
├── index.html                  # 项目首页静态页面模版
└── ...
```

在package.json下可以看到`npm run build`其实执行的是`node build/build.js`,其代码如下：
```js
'use strict' // 开启严格模式
require('./check-versions')() // 检查版本

process.env.NODE_ENV = 'production'

const ora = require('ora') // 在cmd下展示进度的包
const rm = require('rimraf') // 用于删除文件及文件夹的包
const path = require('path') // node内置解析路径的包
const chalk = require('chalk') // 能在cli下输出有颜色字符串的包
const webpack = require('webpack')
const config = require('../config') // 项目配置，默认导入config配置目录下的index.js
const webpackConfig = require('./webpack.prod.conf') // 导入生产环境的webpack配置

const spinner = ora('building for production...')
spinner.start()


// 先将在config配置中的build目录移除，然后再使用webpack构建，即
// path.join(config.build.assetsRoot, config.build.assetsSubDirectory)连接路径，结果就是指定的目标build目录
rm(path.join(config.build.assetsRoot, config.build.assetsSubDirectory), err => {
  if (err) throw err
  // webpack 提供的Node.js API，用于自定义构建或开发流程，
  // 此时所有的报告和错误处理都必须自行实现，webpack 仅仅负责编译的部分。
  webpack(webpackConfig, (err, stats) => {
    spinner.stop()
    if (err) throw err
    // 对每一个文件都输出其状态，同时配置其输出到cmd中的展示内容
    process.stdout.write(stats.toString({
      colors: true,
      modules: false,
      children: false, // If you are using ts-loader, setting this to true will make TypeScript errors show up during build.
      chunks: false,
      chunkModules: false
    }) + '\n\n')

    // 若某个文件打包出错，则报错并提出webpack打包
    if (stats.hasErrors()) {
      console.log(chalk.red('  Build failed with errors.\n'))
      process.exit(1)
    }

    console.log(chalk.cyan('  Build complete.\n'))
    console.log(chalk.yellow(
      '  Tip: built files are meant to be served over an HTTP server.\n' +
      '  Opening index.html over file:// won\'t work.\n'
    ))
  })
})
```

其中config/index.js中关于build的源码如下：
```js
// ...
module.exports = {
  build: {
    // Paths
    assetsRoot: path.resolve(__dirname, '../dist'),
    assetsSubDirectory: 'static',
    // ...
}
// ...
```

其中build/webpack.prod.conf全部源码如下：
```js
'use strict'
const path = require('path')
const utils = require('./utils') // 导入自定义的工具
const webpack = require('webpack')
const config = require('../config') // 导入项目配置文件index.js
const merge = require('webpack-merge') // 导入用于合并webpack的配置的包
const baseWebpackConfig = require('./webpack.base.conf') // 导入公共的webpack配置信息

// webpack打包用到的插件
const CopyWebpackPlugin = require('copy-webpack-plugin')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const ExtractTextPlugin = require('extract-text-webpack-plugin')
const OptimizeCSSPlugin = require('optimize-css-assets-webpack-plugin')
const UglifyJsPlugin = require('uglifyjs-webpack-plugin')
const ImageminPlugin = require('imagemin-webpack-plugin').default

// 检查node环境，若当前为testing环境，则加载测试环境的配置文件，否则加载生产环境的配置
const env = process.env.NODE_ENV === 'testing'
  ? require('../config/test.env')
  : require('../config/prod.env')

const webpackConfig = merge(baseWebpackConfig, {
  module: {
    rules: utils.styleLoaders({
      sourceMap: config.build.productionSourceMap,
      extract: true,
      usePostCSS: true
    })
  },
  devtool: config.build.productionSourceMap ? config.build.devtool : false,
  output: {
    path: config.build.assetsRoot,
    filename: utils.assetsPath('js/[name].[chunkhash].js'),
    chunkFilename: utils.assetsPath('js/[id].[chunkhash].js')
  },
  plugins: [
    // http://vuejs.github.io/vue-loader/en/workflow/production.html
    new webpack.DefinePlugin({
      'process.env': env,
      'BASE_URL': '"/"'
    }),
    new UglifyJsPlugin({
      uglifyOptions: {
        compress: {
          warnings: false
        }
      },
      sourceMap: config.build.productionSourceMap,
      parallel: true
    }),
    // extract css into its own file
    new ExtractTextPlugin({
      filename: utils.assetsPath('css/[name].[contenthash].css'),
      // Setting the following option to `false` will not extract CSS from codesplit chunks.
      // Their CSS will instead be inserted dynamically with style-loader when the codesplit chunk has been loaded by webpack.
      // It's currently set to `true` because we are seeing that sourcemaps are included in the codesplit bundle as well when it's `false`,
      // increasing file size: https://github.com/vuejs-templates/webpack/issues/1110
      allChunks: true,
    }),
    // Compress extracted CSS. We are using this plugin so that possible
    // duplicated CSS from different components can be deduped.
    new OptimizeCSSPlugin({
      cssProcessorOptions: config.build.productionSourceMap
        ? { safe: true, map: { inline: false } }
        : { safe: true }
    }),
    // generate dist index.html with correct asset hash for caching.
    // you can customize output by editing /index.html
    // see https://github.com/ampedandwired/html-webpack-plugin
    new HtmlWebpackPlugin({
      filename: process.env.NODE_ENV === 'testing'
        ? 'index.html'
        : config.build.index,
      template: 'index.html',
      inject: true,
      minify: {
        removeComments: true,
        collapseWhitespace: true,
        removeAttributeQuotes: true
        // more options:
        // https://github.com/kangax/html-minifier#options-quick-reference
      },
      // necessary to consistently work with multiple chunks via CommonsChunkPlugin
      chunksSortMode: 'dependency'
    }),
    // keep module.id stable when vendor modules does not change
    new webpack.HashedModuleIdsPlugin(),
    // enable scope hoisting
    new webpack.optimize.ModuleConcatenationPlugin(),
    // split vendor js into its own file
    new webpack.optimize.CommonsChunkPlugin({
      name: 'vendor',
      minChunks (module) {
        // any required modules inside node_modules are extracted to vendor
        return (
          module.resource &&
          /\.js$/.test(module.resource) &&
          module.resource.indexOf(
            path.join(__dirname, '../node_modules')
          ) === 0
        )
      }
    }),
    // extract webpack runtime and module manifest to its own file in order to
    // prevent vendor hash from being updated whenever app bundle is updated
    new webpack.optimize.CommonsChunkPlugin({
      name: 'manifest',
      minChunks: Infinity
    }),
    // This instance extracts shared chunks from code splitted chunks and bundles them
    // in a separate chunk, similar to the vendor chunk
    // see: https://webpack.js.org/plugins/commons-chunk-plugin/#extra-async-commons-chunk
    new webpack.optimize.CommonsChunkPlugin({
      name: 'app',
      async: 'vendor-async',
      children: true,
      minChunks: 3
    }),

    // Make sure that the plugin is after any plugins that add images
    new ImageminPlugin({
      disable: process.env.NODE_ENV !== 'production',
      pngquant: {
        quality: '95-100'
      }
    }),

    // copy custom static assets
    new CopyWebpackPlugin([
      {
        from: path.resolve(__dirname, '../static'),
        to: config.build.assetsSubDirectory,
        ignore: ['.*']
      }
    ]),

  ]
})

if (config.build.productionGzip) {
  const CompressionWebpackPlugin = require('compression-webpack-plugin')

  webpackConfig.plugins.push(
    new CompressionWebpackPlugin({
      asset: '[path].gz[query]',
      algorithm: 'gzip',
      test: new RegExp(
        '\\.(' +
        config.build.productionGzipExtensions.join('|') +
        ')$'
      ),
      threshold: 10240,
      minRatio: 0.8
    })
  )
}

if (config.build.bundleAnalyzerReport) {
  const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin
  webpackConfig.plugins.push(new BundleAnalyzerPlugin())
}

module.exports = webpackConfig
```


