---
title: npm常用命令及入门笔记
categories:
  - fe
tags:
  - fe
  - npm
date: 2016-10-21 09:35:30
updated: 2016-10-21 09:35:30
---

### npm help
大部分情况使用`install，update，uninstall`即可,也使用`npm help`能查看npm的官方帮助：[npm官方文档](https://docs.npmjs.com/)

**若使用linux，则有可能需要使用sudo执行命令。**

```shell
# 查看npm版本号
npm -v

# 更新npm本身， -g表示全局安装，没有则是本地安装
npm install npm -g
```
全局安装和本地安装的区别：
- 本地安装：将包安装在./node_modules下，若当前运行命令的所在目录没有node_modules文件夹则会自动新建，可以使用`require([modules_name])`引入本地安装的包
- 全局安装：将包安装在/usr/local下或node的安装目录下，可以直接在命令行中使用，也可以通过require()引入。

### cnpm，更换镜像
若使用npm的官方镜像比较慢，则可以选择使用淘宝的npm镜像，也使用淘宝定制的cnpm命令代替默认的npm。
```shell
# 可以考虑更换npm源，用查看源，默认为 https://registry.npmjs.org/
npm config get registry

# 设置为淘宝源可以比较快的安装node包
npm config set registry https://registry.npm.taobao.org

# 全局安装cnpm同时更新仓库镜像
npm install -g cnpm --registry=https://registry.npm.taobao.org

# 使用cnpm安装模块
cnpm install [module name]
```

### 版本号
使用NPM下载和发布代码时都会接触到版本号。NPM使用语义版本号来管理代码
语义版本号分为X.Y.Z三位，分别代表主版本号、次版本号和补丁版本号。当代码变更时，版本号按以下原则更新。
1. 如果只是修复bug，需要更新Z位。
2. 如果是新增了功能，但是向下兼容，需要更新Y位。
3. 如果有大变动，向下不兼容，需要更新X位。
版本号有了这个保证后，在申明第三方包依赖时，除了可依赖于一个固定版本号外，还可依赖于某个范围的版本号。例如"argv": "0.0.x"表示依赖于0.0.x系列的最新版argv。


### 遇到的一些坑

关于node项目中生产的package-lock.json文件，只有npm 5以上才会有：
[说说 npm 5 的新坑](https://toutiao.io/posts/hrihhs/preview)

