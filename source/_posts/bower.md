---
title: bower入门使用
categories:
  - fe
tags:
  - fe
  - bower
date: 2017-04-18 21:40:53
updated: 2017-04-18 21:40:53
---

参考自：[bower解决js的依赖管理](http://blog.fens.me/nodejs-bower-intro/)

使用`npm install -g bower`安装bower

然后使用`bower --help`可以查看帮助信息
```js
Usage:

    bower <command> [<args>] [<options>]
Commands:

    cache                   Manage bower cache
    help                    Display help information about Bower
    home                    Opens a package homepage into your favorite browser

    info                    Info of a particular package
    init                    Interactively create a bower.json file
    install                 Install a package locally
    link                    Symlink a package folder
    list                    List local packages - and possible updates
    login                   Authenticate with GitHub and store credentials
    lookup                  Look up a single package URL by name
    prune                   Removes local extraneous packages
    register                Register a package
    search                  Search for packages by name
    update                  Update a local package
    uninstall               Remove a local package
    unregister              Remove a package from the registry
    version                 Bump a package version
Options:

    -f, --force             Makes various commands more forceful
    -j, --json              Output consumable JSON
    -l, --loglevel          What level of logs to report
    -o, --offline           Do not hit the network
    -q, --quiet             Only output important information
    -s, --silent            Do not output anything, besides errors
    -V, --verbose           Makes output more verbose
    --allow-root            Allows running commands as root
    -v, --version           Output Bower version
    --no-color              Disable colors
See 'bower help <command>' for more information on a specific command.
```


.bowerrc 作为bower的配置文件，比如配置bower目录
```js
{
    "directory": "app/bower_components"
}
```

使用`bower init`可以根据引导新建一个bower.json文件，内部配置了项目的信息,比如：
```js
{
  "name": "ng1-express4",
  "description": "",
  "main": "",
  "authors": [
    "xmoyking <xmoyking@gmail.com>"
  ],
  "license": "MIT",
  "homepage": "",
  "ignore": [
    "**/.*",
    "node_modules",
    "bower_components",
    "test",
    "tests"
  ],
  "dependencies": {
    "angular": "~1.2.12-build.2226",
    "angular-route": "~1.2.12-build.2226",
    "bootstrap": "^3.3.7"
  }
}
```

除了基本的使用之外，bower的管理依赖能帮助解析库与库之间的关系。


也可以在github上发布自己的包，让其他人也能使用这个包，按照流程一步一步做就可以了。

*吐槽一波，bower安装东西实在太慢，不知道是网速问题还是什么问题*