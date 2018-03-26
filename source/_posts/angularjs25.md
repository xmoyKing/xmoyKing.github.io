---
title: AngularJS入门笔记-25-$provider和$injector服务
categories:
  - AngularJS
tags:
  - AngularJS
date: 2017-07-13 11:00:56
updated:
---

当自定义一个组件时ng在背后注入这组件并提供它所依赖的服务，理解这种背后的机制对使用ng很有帮助，并在单元测试中也非常有用。
- 使用$provider.decorator方法对服务进行修饰
- 使用$injecotr服务获取函数声明的依赖
- 使用$rootElement.injector方法不声明依赖，获取$injector服务

### 注册ng组件
$injecotr服务用于注册组件，如服务本身就是一个组件，这些组件可被注入，满足其他组件的依赖（实际上是由$injecotr服务做“注入”工作），一般情况下，$provider服务所定义的方法通过Module暴露出来以提供访问，但有一些特殊的方法不适合通过Module使用。

由$provider服务定义的方法：
- constant(name, value) 定义常量
- decorator(name, service) 定义修饰器
- factory(name, service) 定义服务
- provider(name, service) 定义服务
- service(name, service) 定义服务
- value(name, value) 定义变量服务
通过Module无法访问的方法是decorator, 此方法能“装饰”服务，即在不修改服务本身的情况下，对服务执行前后做一些修改，有点类似拦截器的行为方式。
如下示例，使用decorator改变$log服务的行为：
```html
<html ng-app="exampleApp">
<head>
    <title>Components</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .config(function($provider) {
            $provider.decorator("$log", function ($delegate) {
                $delegate.originalLog = $delegate.log;
                $delegate.log = function (message) {
                    $delegate.originalLog("Decorated: " + message);
                }
                return $delegate;
            });
        })
        .controller("defaultCtrl", function ($scope, $log) {
            $scope.handleClick = function () {
                $log.log("Button Clicked");
            };
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
       <button class="btn btn-primary" ng-click="handleClick()">Click Me!</button>
    </div>
</body>
</html>
```
注意：
1. 在配置函数中声明对$provider服务的依赖
2. $provider.decorator方法的第一个参数是字符串，因为不是声明依赖，所以需要用字符串
3. $delegate表示代理对象，被装饰的服务，本例即$log服务
4. 在函数中对代理对象$delegate服务进行修改后需要返回代理对象$delegate，否则函数会默认返回undefined
![decorator](1.png)

### 管理注入
$injector服务负责确定函数声明的依赖，并提供这些依赖组件。以下是$injector服务定义的方法
- annotate(fn) 获取指定函数的参数，包括那些未声明的服务（未声明则不会响应）
- get(name) 获取指定服务名称的服务对象
- has(name) 如果指定名称的服务存在，则返回true
- invoke(fn, self, locals) 调用指定函数，使用指定的值作为该函数的this并使用指定的非服务参数值
$injector服务是ng的底层核心，一般很少使用到。

一般情况下，js作为弱类型语言，函数参数是不定的，数量和类型都可以变动，所以函数定义时需要指定参数名，同时js缺乏为函数做注解的能力，在ng中，为了解决这种问题，ng使用Annotate方法，用于获取函数已声明的依赖集。
```html
<html ng-app="exampleApp">
<head>
    <title>Components</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope, $injector) {
            var counter = 0;

            var logClick = function ($log, $exceptionHandler, message) {
                if (counter == 0) {
                    $log.log(message);
                    counter++;
                } else {
                    $exceptionHandler("Already clicked");
                }
            }

            $scope.handleClick = function () {
                var deps = $injector.annotate(logClick);
                for (var i = 0; i < deps.length; i++) {
                    console.log("Dependency: " + deps[i]);
                }
            };
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
       <button class="btn btn-primary" ng-click="handleClick()">Click Me!</button>
    </div>
</body>
</html>
```
上例中，函数logClick依赖$log和$exceptionHandler服务，以及一个普通的js函数参数message
![annotate](2.png)
上图显示了，deps数组内的元素为logClick函数声明的依赖和参数，有的时候只需要服务依赖，而不需要普通的参数，则此时可以使用has判断
```js
for (var i = 0; i < deps.length; i++) {
    if ($injector.has(deps[i])) {
        console.log("Dependency: " + deps[i]);
    }
}
```
此时则只会在控制台输出服务依赖，而没有message参数了

通过get方法可以获取服务对象：
```html
<html ng-app="exampleApp">
<head>
    <title>Components</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope, $injector) {
            var counter = 0;

            var logClick = function ($log, $exceptionHandler, message) {
                if (counter == 0) {
                    $log.log(message);
                    counter++;
                } else {
                    $exceptionHandler("Already clicked");
                }
            }

            $scope.handleClick = function () {
                var deps = $injector.annotate(logClick);
                var args = [];
                for (var i = 0; i < deps.length; i++) {
                    if ($injector.has(deps[i])) {
                        args.push($injector.get(deps[i]));
                    } else if (deps[i] == "message") {
                        args.push("Button Clicked");
                    }
                }
                logClick.apply(null, args);
            };
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
       <button class="btn btn-primary" ng-click="handleClick()">Click Me!</button>
    </div>
</body>
</html>
```
连续点击两次则控制台会输出如下截图的内容
![get方法](3.png)
可以发现，其实是显式调用了logClick方法，每次调用的时候都将改变后的args数组传入函数

invoke方法可以找到服务并管理这些服务：
```html
<html ng-app="exampleApp">
<head>
    <title>Components</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope, $injector) {
            var counter = 0;

            var logClick = function ($log, $exceptionHandler, message) {
                if (counter == 0) {
                    $log.log(message);
                    counter++;
                } else {
                    $exceptionHandler("Already clicked");
                }
            }

            $scope.handleClick = function () {
                var localVars = { message: "Button Clicked" };
                $injector.invoke(logClick, null, localVars);
            };
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
       <button class="btn btn-primary" ng-click="handleClick()">Click Me!</button>
    </div>
</body>
</html>
```
传入invoke方法的参数依次是将被调用的函数，this值，以及与函数参数一致的属性的对象，这个对象不是服务依赖。

$rootElement服务提供访问应用了ng-app指令的html元素的方法，它是ng应用的根，$rootElement服务作为jqLite对象表示，即可以通过jqLite的方式定位或修改DOM。$rootElement服务对象由一个injector方法，它返回$injector服务对象。
```html
<html ng-app="exampleApp">
<head>
    <title>Components</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope, $rootElement) {
            var counter = 0;

            var logClick = function ($log, $exceptionHandler, message) {
                if (counter == 0) {
                    $log.log(message);
                    counter++;
                } else {
                    $exceptionHandler("Already clicked");
                }
            }

            $scope.handleClick = function () {
                var localVars = { message: "Button Clicked" };
                $rootElement.injector().invoke(logClick, null, localVars);
            };
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
       <button class="btn btn-primary" ng-click="handleClick()">Click Me!</button>
    </div>
</body>
</html>
```
一般来说是不需要通过$rootElement获取$injector服务的，因为可以直接声明对$injector的依赖。