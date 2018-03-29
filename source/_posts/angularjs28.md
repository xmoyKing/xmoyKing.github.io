---
title: AngularJS巩固实践-28-《AngularJS深度剖析与最佳实践》笔记-需求分析
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
date: 2017-07-20 16:28:41
updated:
---

从本节开始，学习《AngularJS深度剖析与最佳实践》， 主要对前面的学习进行巩固加强，深入理解概念，同时记录一些最佳实践和技巧。

实例是一个小论坛系统，用于读者相互交流。从前端到后台，后台采用Java，业务目标，即一个明确的功能列表，不仅包括“要做什么”，还要包含“不做什么”，防止“需求蔓延”。

同时展示使用FrontJet初始化一个ng项目的组织结构应该是怎么样的，FrontJet是一个基于node的前端工具集, FrontJet 的安装非常简单，使用 `cnpm install -g fj` 即可, fj 是 FrontJet 的缩写,

它主要有以下亮点：
- 可独立安装、升级。
- 去掉了很多选项，直接选用经过实践检验的固定技术栈，简化创建过程。
- 自带一个种子工程，里面包含根据实战经验总结出来的目录结构和开发指南，可用于创建新工程。
- 自动注入项目中的 JavaScript 文件和 Scss 文件，引入 JavaScript 文件时会加上charset="utf-8" 选项。
- 对文件进行增删改时都能正常触发 reload。
- 增加编译 Web font 的功能，即把一个 svg 文件放入 icons 目录，就会自动编译成font 文件（ttf、woff 等），以及相应的 Scss 文件。
- 增加 Forks 功能，可生成针对不同操作系统的文件，开发服务器会根据浏览器所在的操作系统返回相应分支下的文件，这特别适合于手机版调试。
- 增加 Mock 功能，基于 node-restify 库，生成一个内置的 Mock 服务器，可在与真实服务端对接之前提供一个模拟服务器。这些 Mock 数据也会被自动用于单元测试。
- 增加内置的启动为 https 服务的功能，可用于排查 https 的特有问题。
- 增加针对特定 URL 的反向代理、模拟延迟功能。反向代理虽然在 gulp-angular 中也有实现，不过比较粗糙，需要修改 gulp 源码才能工作，将其移到 fj.conf.js 中。模拟延迟则用于模拟真实环境中的网络延迟，以便设计更好的用户体验。
- 在 Linux/Mac 下增加了系统级错误提示框：当 FrontJet 编译过程中发现语法错误时，会通过系统本身的通知功能显示一个错误提示框，以免被忽略。

这个结构中有很多 README.md 文件，用于解释当前目录的结构以及用途，它们不会出现在编译结果中，并且可以随意删除。也可以自行编辑它，用于在项目组中保持共识。

本项目的结构简介如下：

|-- app（源码的根目录）
| |-- animations（自定义动画）
| | |-- README.md
| | `-- ease.js（动画样例）
| |-- app.js（app模块的定义文件）
| |-- components（组件型指令）
| | |-- README.md
| | `-- layout（外框架）
| | |-- _layout.html（模板）
| | |-- _layout.js（控制器）
| | |-- _layout.test.js（与控制器对应的单元测试）
| | |-- _layout.scss（样式）
| | |-- footer.html
| | |-- footer.js
| | |-- footer.scss
| | |-- header.html
| | |-- header.js
| | |-- header.scss
| | |-- menu.html
| | |-- menu.js
| | `-- menu.scss
| |-- configs（配置）
| | |-- README.md
| | |-- config.js（config阶段的代码）
| | |-- router.js（路由定义）
| | `-- run.js（run阶段的代码）
| |-- consts（常量）
| | |-- README.md
| | `-- api.js（API定义）
| |-- controllers（控制器）
| | |-- README.md
| | `-- home（首页）
| | |-- index.html（模板）
| | |-- index.js（控制器）
| | |-- index.scss（样式）
| | |-- notFound.html（模板）
| | |-- notFound.js（控制器）
| | `-- notFound.scss（样式）
| |-- decorators（装饰器型指令）
| | `-- README.md
| |-- favicon.ico（网站图标）
| |-- filters（过滤器）
| | `-- README.md
| |-- forks（系统分支）
| | |-- README.md
| | |-- android（适用于安卓浏览器的文件）
| | | `-- README.md
| | |-- default（适用于其他系统的文件）
| | | `-- README.md
| | `-- ios（适用于iOS浏览器的文件）
| | `-- README.md
| |-- icons（svg图标源文件）
| | `-- README.md
| |-- images（普通图片）
| | |-- README.md
| | `-- logo.png
| |-- index.html（首页）
| |-- libraries（第三方非Angular库、非bower文件，会被最先引用）
| | `-- README.md
| |-- services（服务）
| | |-- interceptors（拦截器，用于过滤通过Ajax上传或下载的数据）
| | | |-- AuthHandler.js（401的处理器）
| | | |-- ErrorHandler.js（其他4xx、5xx错误码的处理器）
| | | |-- LoadingHandler.js（加载中界面）
| | | `-- README.md
| | |-- sao（服务访问对象）
| | | `-- README.md
| | `-- utils（工具类服务）
| | `-- README.md
| `-- styles（样式定义）
| |-- README.md
| |-- _app.scss（应用程序的定义，自动引入所有具体页面的Scss)
| |-- _bootstrap.scss（对Bootstrap的样式重定义）
| |-- _common.scss（具有跨项目复用价值的样式）
| |-- _icons.scss（根据svg图标编译出的样式文件）
| |-- _variables.scss（变量定义，包括对Bootstrap的覆盖式样式定义）
| `-- main.scss（总的CSS文件，用于依次引入其他文件，一般不在此处定义样式）
|-- bower.json（bower库的名称及版本列表）
|-- bower_components（bower库文件）
|-- dist（编译结果/供最终发布的文件）
|-- fj.conf.js（FrontJet的配置文件，详情见注释）
|-- mock（Mock服务器）
| |-- README.md
| |-- package.json
| |-- resources（资源数据定义）
| | `-- users.js
| |-- routers（服务端路由实现）
| | `-- users.js
| |-- routers.js（路由列表）
| |-- server.js（服务器启动文件）
| `-- utils（工具类）
| `-- resourceMixin.js
|-- test（测试）
| |-- e2e（端到端测试）
| | |-- demo.js
| | `-- readme.md
| |-- karma.conf.js（Karma的配置文件，用于单元测试）
| |-- protractor.conf.js（Protractor的配置文件，用于端到端测试）
| `-- unit（单元测试）
| `-- readme.md
|-- .bowerrc（bower的配置文件，用于指定bower路径等）
|-- .editorconfig（编辑器配置，用于在不同的编辑器之间统一缩进等代码风格）
|-- .gitignore（Git的忽略列表，匹配的不会被添加到Git库中）
|-- .jshintrc（JavaScript代码风格检查工具jshint的配置文件，用于定制代码检查规则）
|-- tsd.json（第三方库名称及版本列表）
`-- typings（第三方库定义）

其中 app 目录的内部结构都是可以任意调整的，不会影响 FrontJet 的运行。当要对传统项目使用 FrontJet 时，可以将其源文件拷贝到 app 目录下。