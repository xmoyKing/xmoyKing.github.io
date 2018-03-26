---
title: AngularJS入门笔记-7-AngularJS+MongoDB+Nodejs
categories:
  - AngularJS
tags:
  - AngularJS
date: 2017-05-03 15:56:41
updated: 2017-05-03 15:56:41
---

上一本权威指南初略的刷了一遍后找出了很多命令和知识点，这次大致刷了一遍《AngularJS开发下一代Web应用》，发现还是有些问题：

第三章没有图片，无法理清逻辑，第四章的测试小结需要在熟悉angular练习小项目之后再重新看一遍。

使用Jasmine进行单元测试，以及用angular-mock模拟数据。

所以先暂停刷书，直接上手开干！

## 《Nodejs+MongoDB+AngularJS Web开发》

Angularjs提供了一些基本的全局使用的工具方法，这些方法可以通过angular对象直接访问,比如深拷贝方法：
```js
var myCopy = angular.copy(myObj);
```
全局API：
- copy(src, [dst]) 创建一个src对象/数组的深拷贝，dst为可选的目的对象
- element(element) 返回一个jQuery包装后的DOM元素，若在ng之前加载jQuery库，则返回对象是一个完成的jQuery对象，否则为ng内置的精简版
- equals(o1, o2) 比较对象，使用 === 实现
- extend(dst, src) 继承
- forEach(obj, iterator, [context]) 遍历对象/数组，（js原生只能对数组使用这个方法），iterator是一个函数，接受两个参数value， key
- fromJson(json) 将JSON字符串转换为一个js对象
- toJson(obj) 将js对象转换为JSON字符串
- isArray(value)
- isDate(value)
- isDefined(value)
- isElement(value) 包括DOM，jQuery元素，
- isFunction(value)
- isNumber(value)
- isObject(value)
- isString(value)
- isUndefined(value)
- lowercase(string)
- uppercase(string)

一个基本的express + AngularJS 的入门示例：
目录结构为：
/app_server.js 项目启动入口
/static/first.html 静态页面
/static/js/first.js ng代码

/app_server.js文件内容：
```js
var express = require('express');
var app = express();
app.use('/', express.static('./static')).
    use('/images', express.static( '../images')).
    use('/lib', express.static( '../lib'));
app.listen(80);
```

/static/first.html 内容：
```html
<!doctype html>

<html ng-app="firstApp">
  <head>
    <title>First AngularJS App</title>
  </head>
  <body>
    <div ng-controller="FirstController">
      <span>Name:</span>
      <input type="text" ng-model="first">
      <input type="text" ng-model="last">
      <button ng-click='updateMessage()'>Message</button>
      <hr>
      {{heading + message}}
    </div>
    <script src="http://code.angularjs.org/1.2.9/angular.min.js"></script>
    <script src="/js/first.js"></script>
  </body>
</html>
```

/static/js/first.js 内容：
```js
var firstApp = angular.module('firstApp', []);
firstApp.controller('FirstController', function($scope) {
  $scope.first = 'Some';
  $scope.last = 'One';
  $scope.heading = 'Message: ';
  $scope.updateMessage = function() {
    $scope.message = 'Hello ' + $scope.first +' '+ $scope.last + '!';
  };
});
```