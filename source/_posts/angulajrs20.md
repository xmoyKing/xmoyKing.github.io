---
title: angulajrs入门笔记-20-针对错误、表达式、全局对象的服务
categories:
  - fe
tags:
  - fe
date: 2017-05-27 15:27:49
updated:
---

ng内置了对全局对象，处理异常，显示危险数据，处理表达式相关的服务。

### DOM API全局对象服务
ng内置的全局对象服务的目的是为了使测试变得简单，单元测试最重要的功能就是隔离一段代码并单独测试其行为而无需测试它所依赖的组件。浏览器中的DOM API通过全局对象提供接口，如document和window，但这种暴露全局对象的方式不适合单元测试，所以使用$document这样的服务能够不用DOM API时也能写ng代码。

#### $window服务
$window服务使用简单，和原生的使用无差别，ng并没有增强或改变这个全局对象
```js
angular.module("exampleApp", [])
.controller("defaultCtrl", function ($scope, $window) {
    $scope.displayAlert = function(msg) {
        $window.alert(msg);
    }
});
```

#### $document服务
$document服务是一个包含原生DOM API的jqLite对象
```js
angular.module("exampleApp", [])
.controller("defaultCtrl", function ($scope, $window, $document) {
    $document.find("button").on("click", function (event) {
        $window.alert(event.target.innerText);
    });
});
```

#### $interval和$timeout服务
$interval和$timeout服务包含了一些增强的功能
- fn 定时执行的函数
- delay fn被执行前的延迟事件
- count $interval执行循环次数，默认0表示无限制
- InvokeApply 默认为true，表示fn将和scope.$apply一同执行，
```js
angular.module("exampleApp", [])
.controller("defaultCtrl", function ($scope, $interval) {
    $interval(function () {
        $scope.time = new Date().toTimeString();
    }, 2000); // 省略count和InvokeApply参数，使用默认
});
```

#### $location服务
$location服务增强了原生location属性，提供访问当前URL的入口。它操作第一个`#`号后面的URL部分，即它不会整体刷新页面，ng在#后重建了完整的URL
```
http://host.com/app.html#/cities/london?select=hotels#north
```
$location服务提供的一些方法：
- absUrl() 放回当前文档的完整url，包括第一个#前的部分
- hash(target) 获取或设置url的hash部分
- host() 返回完整url的主机名称（host.com）
- path(target) 获取或设置url路径
- port() 返回端口号，默认为80
- protocol() 返回协议，一般为http 
- replace() 跳转
- search(term, params) 获取或设置搜索项
- url(target) 或者或设置path、search、hash
- $locationChangeStart 事件，url被改变前触发，可以用过event对象中的preventDefault阻止改变url
- $locationChangeSuccess 事件，url被改变后触发

```html
<html ng-app="exampleApp">
<head>
    <title>DOM API Services</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope, $location) {

            $scope.$on("$locationChangeSuccess", function (event, newUrl) {
                $scope.url = newUrl;
            });

            $scope.setUrl = function (component) {
                switch (component) {
                    case "reset":
                        $location.path("");
                        $location.hash("");
                        $location.search("");
                        break;
                    case "path":
                        $location.path("/cities/london");
                        break;
                    case "hash":
                        $location.hash("north");
                        break;
                    case "search":
                        $location.search("select", "hotels");
                        break;
                    case "url":
                        $location.url("/cities/london?select=hotels#north");
                        break;
                }
            }
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="panel panel-default">
        <h4 class="panel-heading">URL</h4>
        <div class="panel-body">
            <p>The URL is: {{url}}</p>
            <div class="btn-group ">
                <button class="btn btn-primary" ng-click="setUrl('reset')">Reset</button>
                <button class="btn btn-primary" ng-click="setUrl('path')">Path</button>
                <button class="btn btn-primary" ng-click="setUrl('hash')">Hash</button>
                <button class="btn btn-primary" 
                     ng-click="setUrl('search')">Search</button>
                <button class="btn btn-primary" ng-click="setUrl('url')">URL</button>
            </div>
        </div>
    </div>
</body>
</html>
```
HTML5的History API提供了更优雅的方式来处理url，能改变url且页面也不会发生重载，使用$location服务的提供器，$locationProvider启用。
```js
.config(function($locationProvider) {
    $locationProvider.html5Mode(true);
})
```
如下是依次点击如下按钮对url的影响：
1. Reset http://host.com/
2. Path http://host.com/cities/london
3. Hash http://host.com/cities/london#north
4. Search http://host.com/cities/london?select=hotels#north
5. URL http://host.com/cities/london?select=hotels#north

但是History API在各浏览器中的实现不一致，同时旧浏览器不支持，所以需要在用之前测试
```js
.config(function ($locationProvider) {
    if (window.history && history.pushState) {
        $locationProvider.html5Mode(true);
    }
})
```

$anchorScroll服务滚动浏览器窗口到显示id与$location.hash一致的元素处,同时通过服务提供器禁用这个功能
```html
<html ng-app="exampleApp">
<head>
    <title>DOM API Services</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .config(function ($anchorScrollProvider) {
            $anchorScrollProvider.disableAutoScrolling();
        })
        .controller("defaultCtrl", function ($scope, $location, $anchorScroll) {

            $scope.itemCount = 50;
            $scope.items = [];

            for (var i = 0; i < $scope.itemCount; i++) {
                $scope.items[i] = "Item " + i;
            }
                
            $scope.show = function(id) {
                $location.hash(id);
                if (id == "bottom") {
                    $anchorScroll();
                }
            }
        });
    </script>

</head>
<body ng-controller="defaultCtrl">
    <div class="panel panel-default">
        <h4 class="panel-heading">URL</h4>
        <div class="panel-body">
            <p id="top">This is the top</p>
            <button class="btn btn-primary" ng-click="show('bottom')">
                Go to Bottom</button>
            <p>
                <ul>
                    <li ng-repeat="item in items">{{item}}</li>
                </ul>
            </p>
            <p id="bottom">This is the bottom</p>
            <button class="btn btn-primary" ng-click="show('top')">Go to Top</button>
        </div>
    </div>
</body>
</html>
```
当show的参数为bottom时，调用$anchorScroll服务，否则不调用。具体表现为：浏览器能在单击Go to Bottom按钮时滚动到底部，但是单击Go to Top按钮时不滚动到顶部