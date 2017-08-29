---
title: angularjs入门笔记-26-单元测试1
categories:
  - fe
tags:
  - fe
  - angularjs
  - jasmine
date: 2017-07-15 09:17:43
updated:
---

单元测试能让ng从框架中分离代码，为完整测试提供支持。
- 使用Jasmine的describe、befireEach、it和expect函数写基本的单元测试
- 使用angular.mock.module方法载入待测模块，使用angular.mock.inject方法解决依赖
- 在ngMocks模块中使用$httpBackend服务，仿造HTTP请求
- 在ngMocks模块中使用$interval和$timeout服务,仿造超时和间隔
- 在ngMocks模块中使用$log服务，测试日志
- 使用$filter服务实例化过滤器，测试过滤器
- 使用$compile服务生成一个函数，该函数传入作用域参数可生成能使用jqLite的HTML，测试指令
- 使用angular.mock.inject方法解决被测试服务的依赖，测试服务

关于Karma的一些问题：
Karma内置了三种流行的测试框架：Jasmine、Mocha和QUnit,可以检测文件变化并在浏览器中自动执行测试代码。每一个项目都需配置并初始化后使用karma：
```js
karma init karma.config.js
```
karma.config.js的内容为：
```js
// Karma configuration
// Generated on Sun Dec 01 2013 16:50:31 GMT+0000 (GMT Standard Time)

module.exports = function(config) {
  config.set({

    // base path, that will be used to resolve files and exclude
    basePath: '',


    // frameworks to use
    frameworks: ['jasmine'],


    // list of files / patterns to load in the browser
    files: [
      'angular.js',
      'angular-mocks.js',
      '*.js',
      'tests/*.js'
    ],


    // list of files to exclude
    exclude: [
      
    ],


    // test results reporter to use
    // possible values: 'dots', 'progress', 'junit', 'growl', 'coverage'
    reporters: ['progress'],


    // web server port
    port: 9876,


    // enable / disable colors in the output (reporters and logs)
    colors: true,


    // level of logging
    // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
    logLevel: config.LOG_INFO,


    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: true,


    // Start these browsers, currently available:
    // - Chrome
    // - ChromeCanary
    // - Firefox
    // - Opera (has to be installed with `npm install karma-opera-launcher`)
    // - Safari (only Mac; has to be installed with `npm install karma-safari-launcher`)
    // - PhantomJS
    // - IE (only Windows; has to be installed with `npm install karma-ie-launcher`)
    browsers: ['Chrome'],


    // If browser does not capture in given timeout [ms], kill it
    captureTimeout: 60000,


    // Continuous Integration mode
    // if true, it capture browsers, run tests and exit
    singleRun: false
  });
};
```


### 测试示例

创建一个app.html,效果为点击按钮计数增加，下例使用Jasmine作为测试框架。
```html
<html ng-app="exampleApp">
<head>
    <title>Example</title>
    <script src="angular.js"></script>
    <script src="app.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
</head>
<body ng-controller="defaultCtrl">
    <div class="panel panel-default">
        <div class="panel-body">
            <p>Counter: {{counter}}</p>
            <p>
                <button class="btn btn-primary" 
                    ng-click="incrementCounter()">Increment</button>
            </p>
        </div>
    </div>
</body>
</html>
```
app.js
```js
angular.module("exampleApp", [])
    .controller("defaultCtrl", function ($scope) {

        $scope.counter = 0;

        $scope.incrementCounter = function() {
            $scope.counter++;
        }
    });
```

在tests文件夹中添加一个test.js文件：
```js
describe("First Test", function () {

    // Arrange (set up a scenario)
    var counter;

    beforeEach(function () {
        counter = 0;
    });

    it("increments value", function () { // 测试通过
        // Act (attempt the operation)
        counter++;
        // Assert (verify the result)
        expect(counter).toEqual(1);
    })

    it("decrements value", function () { // 测试失败
        // Act (attempt the operation)
        counter--;
        // Assert (verify the result)
        expect(counter).toEqual(0);
    })
});
```
写单元测试时，流程为 arrange/act/assert A/A/A模式 (准备/行动/断言) ，准备是指设置测试所需的一些变量或预置值，行动是指执行测试，断言是指检查结果确保正确性。

Jasmine测试使用js函数，提供如下的方法：
- describe 可选，将测试分组，利用组织测试代码
- beforeEach 在测试前执行的函数，即准备阶段
- it 执行函数测试，即行动阶段
- expect 识别测试结果，即断言阶段的第一部分
- toEqual 比较结果和期望值是否相等，断言阶段的第二部分

其中用于评估结果的函数很多，不止toEqual一种，还有如下一些方法：
- expect(x).toEqual(val) 断言x与val值相当（不需要是同一对象）
- expect(x).toBe(obj) 断言x与obj是同一个对象
- expect(x).toMatch(reg) 断言x匹配指定的正则表达式
- expect(x).toBeDefined() 断言x已定义
- expect(x).toBeUndefined() 断言x未定义
- expect(x).toBeNull() 断言x是null
- expect(x).toBeTruthy() 断言x是true或等价于true
- expect(x).toBeFalsy() 断言x是false或等价于false
- expect(x).toContain(y) 断言x是包含y的字符串
- expect(x).toBeGreaterThan(y) 断言x大于y
- expect(x).not.toEqual(val) 断言x与val值不同，即加上not表示与后面的判断相反

### 仿造对象
仿造（mocking）是创建在应用中替换关键组件对象的过程，以此进行单元测试，比如，需要测试使用$http服务发出ajax请求的控制器行为，该行为依赖其他组件和系统，控制器所属的ng模块、$http服务、处理请求的服务、数据库。一旦测试失败，却没办法直接定位到问题的源头，因为导致失败的地方很多，有可能是数据库，也有可能是$http服务，也有可能是处理请求的问题。

此时测试就非常艰难，所以需要将这些依赖组件替换为仿造对象（mock object），这些仿造对象实现所需组件的API，生成仿造的、预计的结果，无需重新配置测试服务器，数据库，网络，可以快捷的改变仿造对象来测试不同场景下的使用情况。

ngMock模块包含的仿造对象：
- angular.mock 用于创建仿造模块并解决依赖。
- $exceptionHandler 仿造$exceptionHandler服务，抛出异常
- $interval 仿造$interval服务
- $timeout 仿造$timeout，直接触发预计函数
- $log 仿造$log

#### 测试控制器
angular.mock对象定义的方法：
- module(name) 载入name模块
- inject(fn) 解决依赖并注入
- dump(object) 序列化ng对象（如服务对象）

除了angular.mock，ng还提供了一些其他方法和服务用于单元测试
- $rootScope.new() 创建新作用域
- $controller(name) 创建指定控制器的实例
- $filter(name) 创建指定过滤器的实例

如下的tests/controllerTest.js内容，用于测试控制器：
```js
describe("Controller Test", function () {

    // Arrange
    var mockScope = {};
    var controller;
    // 1.准备包含控制器的模块，一个简写的方式是：beforeEach(module("exampleApp"));
    beforeEach(angular.mock.module("exampleApp"));
    // 2.解决依赖，并注入服务，将作用域设置为mockScope
    beforeEach(angular.mock.inject(function ($controller, $rootScope) {
        mockScope = $rootScope.$new();
        controller = $controller("defaultCtrl", { 
            $scope: mockScope
        });
    }));
    // 3.执行测试并对比结果
    // Act and Assess
    it("Creates variable", function () {
        expect(mockScope.counter).toEqual(0);
    })

    it("Increments counter", function () {
        mockScope.incrementCounter();
        expect(mockScope.counter).toEqual(1);
    });
});
```

#### 仿造HTTP响应
$httpBackend服务提供底层API用于$http服务产生ajax请求（也可用于$resource服务，其反依赖于$http）. $httpBackend仿造服务包含于ngMocks模块中，使用流程很简单
1. 定义期望的请求及其响应
2. 发送响应
3. 检测所有已产生的请求
4. 评估结果

在app.js中添加ajax请求：
```js
angular.module("exampleApp", [])
    .controller("defaultCtrl", function ($scope, $http) {

        $http.get("productData.json").success(function (data) {
            $scope.products = data;
        });

        $scope.counter = 0;

        $scope.incrementCounter = function() {
            $scope.counter++;
        }
    });
```
然后修改controllerTest.js，用于测试ajax请求：
```js
describe("Controller Test", function () {

    // Arrange
    var mockScope, controller, backend;

    beforeEach(angular.mock.module("exampleApp"));

    beforeEach(angular.mock.inject(function ($httpBackend) {
        backend = $httpBackend;
        // 1. 定义期望的请求及其响应
        backend.expect("GET", "productData.json").respond(
        [{ "name": "Apples", "category": "Fruit", "price": 1.20 },
        { "name": "Bananas", "category": "Fruit", "price": 2.42 },
        { "name": "Pears", "category": "Fruit", "price": 2.02 }]);
    }));

    beforeEach(angular.mock.inject(function ($controller, $rootScope, $http) {
        mockScope = $rootScope.$new();
        $controller("defaultCtrl", {
            $scope: mockScope,
            $http: $http
        });
        // 2. 发送响应
        backend.flush();
    }));

    // Act and Assess
    it("Creates variable", function () {
        expect(mockScope.counter).toEqual(0);
    })

    it("Increments counter", function () {
        mockScope.incrementCounter();
        expect(mockScope.counter).toEqual(1);
    });

    // 3. 检测所有已产生的请求
    it("Makes an Ajax request", function () {
        backend.verifyNoOutstandingExpectation();
    });

    // 4. 评估结果
    it("Processes the data", function () {
        expect(mockScope.products).toBeDefined();
        expect(mockScope.products.length).toEqual(3);
    });

    it("Preserves the data order", function () {
        expect(mockScope.products[0].name).toEqual("Apples");
        expect(mockScope.products[1].name).toEqual("Bananas");
        expect(mockScope.products[2].name).toEqual("Pears");
    });
});
```
$httpBackend仿造服务提供能匹配$http服务所需的API，定义了如下的方法：
- expect(method, url, data, headers) 定义期望的请求，它匹配方法和URL(包含可选数据和头部的匹配)
- flush() / flush(count) 发回等待结果（可选参数指定响应数量）
- resetExpectations() 重置所有期望
- verifyNoOutstandingExpectation() 检测所有已接收到的期望的请求
- respond(data) / respond（status, data, headers） 为请求定义响应
