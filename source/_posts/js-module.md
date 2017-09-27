---
title: javascript模块化
categories:
  - js
tags:
  - js
  - module
date: 2016-11-07 19:05:34
updated: 2016-11-07 19:05:34
---

了解javascript的模块化,以及AMD,CMD的异同, SeaJS, RequireJS的简单介绍和应用

起源于nodejs, 将所有js分块,每一个块标准相同(即接口相同), 用时如同乐高将每一个块组合起来即可.

### AMD规范
AMD是RequireJS对模块化的规范
1. 异步加载,依赖前置,提前执行
2. 定义模块方法: `define(['require','foo'], function(){ return })`
3. 加载模块方法(依赖前置): `require(['foo', 'bar'], function(foo, bar){ });`

### CMD规范
CMD是SeaJS对模块化的规范
1. 同步加载,依赖就近,延迟执行
2. 定义模块使用exports导出`define(function(require, exports, module){})`,其中module存储当前模块的一些对象
3. 可使用require(modulename)直接引入, 也可使用require.async(modulename, callback)异步引入

除了AMD和CMD规范, 还有CommonJS Modules/2.0 规范

### SeaJS入门上手
最新版本为3.0，于2014年6月更新，已经很久没更新

目录组织结构:
```
js
  sea.js
  m1.js
  m2.js
  main.js
index.html
```

```html
<script type="text/javascript" src="js/sea.js"></script>
<script type="text/javascript">
    seajs.config({
        base: './js/', // 配置模块基本目录
        alias: { // 配置别名
            jquery: 'jquery.js'
        }
    });
    seajs.use('main'); //加载入口模块
</script>
```

```js
// main.js
define(function (require, exports, module) {
    // 异步加载
    require.async('m2', function(m){
        m.do2();
    });
    
    // 同步导入;
    var index = require('m1');
    index.fun1();

})
```

```js
// m1.js
define(function (require, exports, module) {
    // 通过exports导出某个函数,
    exports.fun1 = function () {
        console.log('fun1');
    }
    exports.fun2 = function () {
        console.log('fun2');
    }
});

```

```js
// m2.js
define(function (require, exports, module) {
    // 通过module.exports提供整个对象,包括内部的所有函数和变量
    // 与上面exports的导出方式不可公用
    var multi = {}; // 预先定义导出对象
    multi.do1 = function(){
        console.log('do1');
    }
    multi.do2 = function(){
        console.log('do2');
    }

    module.exports = multi;
})
```
[SeaJS demo代码下载地址](./SeaJS.zip)

DEMO截图：
Chrome下打印执行结果:
![Chrome下打印执行结果](1.png)

Chorme下加载文件顺序：
![Chorme下加载文件顺序](2.png)


### RequireJS入门上手
RequireJS和SeaJS有相似的地方，但是也有不同之处，但是实现的功能都是一样的，那就是将js模块化,写法和思想上有不同。

目录组织结构:
```
js
  require.js
  m1.js
  m2.js
  main.js
index.html
```

```html
<script type="text/javascript" data-main="js/main.js" src="js/require.js"></script>
```

```js
// main.js
define(['require','main'], function (require) {
    console.log('main');
    require(['m1', 'm2'], function(m1, m2){
        console.log(m1);
        console.log(m2);
    });
});
```

```js
// m1.js
define(['require','m1'], function (require) {
    console.log('m1');
    return function(){
        console.log('m1 return');
    }
});
```

```js
// m2.js
define(['require','m2'], function (require) {
    console.log('m2');
    var init = function(){
        console.log('m2 init');
    }
    return init;
});
```

[RequireJS demo代码下载地址](./RequireJS.zip)


执行结果截图：
![RequireJS执行结果](3.png)
