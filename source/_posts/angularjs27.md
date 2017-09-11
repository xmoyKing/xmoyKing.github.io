---
title: angularjs入门笔记-27-单元测试2
categories:
  - angularjs
tags:
  - angularjs
  - jasmine
date: 2017-07-18 13:52:40
updated:
---

接上一个"单元测试1"的内容：

#### 仿造定时
$interval和$timeout仿造服务定义的方法能明确的触发由测试代码注册的回调函数。
- flush(millisecond) 使计时器快进的毫秒数，$timeout和$interval服务都提供此方法 
- verifyNoPendingTasks() 查看是否还有未被调用的回调函数，仅$timeout服务提供此方法

在app.js中添加定时：
```js
angular.module("exampleApp", [])
    .controller("defaultCtrl", function ($scope, $http, $interval, $timeout) {

        $scope.intervalCounter = 0;
        $scope.timerCounter = 0;

        $interval(function () {
            $scope.intervalCounter++;
        }, 5000, 10);

        $timeout(function () {
            $scope.timerCounter++;
        }, 5000);

        $http.get("productData.json").success(function (data) {
            $scope.products = data;
        });

        $scope.counter = 0;

        $scope.incrementCounter = function() {
            $scope.counter++;
        }
    });
```
在controllerTest.js添加测试:
```js
describe("Controller Test", function () {

    // Arrange
    var mockScope, controller, backend, mockInterval, mockTimeout;

    beforeEach(angular.mock.module("exampleApp"));

    beforeEach(angular.mock.inject(function ($httpBackend) {
        backend = $httpBackend;
        backend.expect("GET", "productData.json").respond(
        [{ "name": "Apples", "category": "Fruit", "price": 1.20 },
        { "name": "Bananas", "category": "Fruit", "price": 2.42 },
        { "name": "Pears", "category": "Fruit", "price": 2.02 }]);
    }));
    // 1.准备定时器测试预设参数
    beforeEach(angular.mock.inject(function ($controller, $rootScope, 
           $http, $interval, $timeout) {
        mockScope = $rootScope.$new();
        mockInterval = $interval;
        mockTimeout = $timeout;
        $controller("defaultCtrl", {
            $scope: mockScope,
            $http: $http,
            $interval: mockInterval,
            $timeout: mockTimeout
        });
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

    it("Makes an Ajax request", function () {
        backend.verifyNoOutstandingExpectation();
    });

    it("Processes the data", function () {
        expect(mockScope.products).toBeDefined();
        expect(mockScope.products.length).toEqual(3);
    });

    it("Preserves the data order", function () {
        expect(mockScope.products[0].name).toEqual("Apples");
        expect(mockScope.products[1].name).toEqual("Bananas");
        expect(mockScope.products[2].name).toEqual("Pears");
    });
    // 2.执行
    it("Limits interval to 10 updates", function () {
        for (var i = 0; i < 11; i++) {
            mockInterval.flush(5000);
        }
        expect(mockScope.intervalCounter).toEqual(10);
    });

    it("Increments timer counter", function () {
        mockTimeout.flush(5000);
        expect(mockScope.timerCounter).toEqual(1);
    });
});
```

#### 测试日志
$log仿造服务对日志信息进行跟踪，并通过它测试单元代码是否记录正确的信息。
修改app.js，每当$interval服务所注册的回调函数被调用时就记录信息,使用$log仿造服务确定日志消息的数量正确：
```js
angular.module("exampleApp", [])
    .controller("defaultCtrl", function ($scope, $http, $interval, $timeout, $log) {

        $scope.intervalCounter = 0;
        $scope.timerCounter = 0;

        $interval(function () {
            $scope.intervalCounter++;
        }, 5, 10);

        $timeout(function () {
            $scope.timerCounter++;
        }, 5);

        $http.get("productData.json").success(function (data) {
            $scope.products = data;
            $log.log("There are " + data.length + " items");
        });

        $scope.counter = 0;

        $scope.incrementCounter = function() {
            $scope.counter++;
        }
    });
```
当控制器工厂函数接收ajax响应时，它就会向$log.log方法写入信息，在单元测试中，读取$log.log.logs数组，其保存着$log.log写入的信息。
修改后的controllerTest.js内容：
```js
describe("Controller Test", function () {

    // Arrange
    var mockScope, controller, backend, mockInterval, mockTimeout, mockLog;

    beforeEach(angular.mock.module("exampleApp"));

    beforeEach(angular.mock.inject(function ($httpBackend) {
        backend = $httpBackend;
        backend.expect("GET", "productData.json").respond(
        [{ "name": "Apples", "category": "Fruit", "price": 1.20 },
        { "name": "Bananas", "category": "Fruit", "price": 2.42 },
        { "name": "Pears", "category": "Fruit", "price": 2.02 }]);
    }));

    beforeEach(angular.mock.inject(function ($controller, $rootScope,
            $http, $interval, $timeout, $log) {
        mockScope = $rootScope.$new();
        mockInterval = $interval;
        mockTimeout = $timeout;
        mockLog = $log;
        $controller("defaultCtrl", {
            $scope: mockScope,
            $http: $http,
            $interval: mockInterval,
            $timeout: mockTimeout,
            $log: mockLog
        });
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

    it("Makes an Ajax request", function () {
        backend.verifyNoOutstandingExpectation();
    });

    it("Processes the data", function () {
        expect(mockScope.products).toBeDefined();
        expect(mockScope.products.length).toEqual(3);
    });

    it("Preserves the data order", function () {
        expect(mockScope.products[0].name).toEqual("Apples");
        expect(mockScope.products[1].name).toEqual("Bananas");
        expect(mockScope.products[2].name).toEqual("Pears");
    });

    it("Limits interval to 10 updates", function () {
        for (var i = 0; i < 11; i++) {
            mockInterval.flush(5000);
        }
        expect(mockScope.intervalCounter).toEqual(10);
    });

    it("Increments timer counter", function () {
        mockTimeout.flush(5000);
        expect(mockScope.timerCounter).toEqual(1);
    });

    it("Writes log messages", function () {
        expect(mockLog.log.logs.length).toEqual(1);
    });

});
```
除了$log.log方法，$log服务还定义了如下两个方法：
- assertEmpty() 检测是否为空，任何日志的写入都会导致异常
- reset() 清楚存储的信息

#### 测试过滤器
向app.js中添加过滤器：
```js
angular.module("exampleApp", [])
    .controller("defaultCtrl", function ($scope, $http, $interval, $timeout, $log) {

        $scope.intervalCounter = 0;
        $scope.timerCounter = 0;

        $interval(function () {
            $scope.intervalCounter++;
        }, 5, 10);

        $timeout(function () {
            $scope.timerCounter++;
        }, 5);

        $http.get("productData.json").success(function (data) {
            $scope.products = data;
            $log.log("There are " + data.length + " items");
        });

        $scope.counter = 0;

        $scope.incrementCounter = function() {
            $scope.counter++;
        }
    })
    .filter("labelCase", function () {
        return function (value, reverse) {
            if (angular.isString(value)) {
                var intermediate = reverse ? value.toUpperCase() : value.toLowerCase();
                return (reverse ? intermediate[0].toLowerCase() :
                    intermediate[0].toUpperCase()) + intermediate.substr(1);
            } else {
                return value;
            }
        };
    });
```
然后在tests/filterTest.js中创建测试过滤器的测试用例：
```js
describe("Filter Tests", function () {
    // 1.定义一个变量，用于保存$filter服务实例
    var filterInstance;

    beforeEach(angular.mock.module("exampleApp"));
    // 2.用inject方法获取$filter服务实例，将其赋值给filterInstance
    beforeEach(angular.mock.inject(function ($filter) {
        filterInstance = $filter("labelCase");
    }));
    // 3.执行测试并比对结果
    it("Changes case", function () {
        var result = filterInstance("test phrase");
        expect(result).toEqual("Test phrase");
    });

    it("Reverse case", function () {
        var result = filterInstance("test phrase", true);
        expect(result).toEqual("tEST PHRASE");
    });

});
```

#### 测试指令
由于指令可修改DOM结构，所以测试指令比较不一样，需要依赖jqLite和$complie服务。
在app.js中添加指令，该指令用于生成一个无序列表ul
```js
angular.module("exampleApp", [])
    .controller("defaultCtrl", function ($scope, $http, $interval, $timeout, $log) {

        $scope.intervalCounter = 0;
        $scope.timerCounter = 0;

        $interval(function () {
            $scope.intervalCounter++;
        }, 5, 10);

        $timeout(function () {
            $scope.timerCounter++;
        }, 5);

        $http.get("productData.json").success(function (data) {
            $scope.products = data;
            $log.log("There are " + data.length + " items");
        });

        $scope.counter = 0;

        $scope.incrementCounter = function () {
            $scope.counter++;
        }
    })
    .filter("labelCase", function () {
        return function (value, reverse) {
            if (angular.isString(value)) {
                var intermediate = reverse ? value.toUpperCase() : value.toLowerCase();
                return (reverse ? intermediate[0].toLowerCase() :
                    intermediate[0].toUpperCase()) + intermediate.substr(1);
            } else {
                return value;
            }
        };
    })
    .directive("unorderedList", function () {
        return function (scope, element, attrs) {
            var data = scope[attrs["unorderedList"]];
            if (angular.isArray(data)) {
                var listElem = angular.element("<ul>");
                element.append(listElem);
                for (var i = 0; i < data.length; i++) {
                    listElem.append(angular.element('<li>').text(data[i].name));
                }
            }
        }
    });
```
在tests/directiveTest.js中添加测试指令的代码：
```js
describe("Directive Tests", function () {
    // 1.定义变量，用于保存作用域和$compile服务实例
    var mockScope;
    var compileService;

    beforeEach(angular.mock.module("exampleApp"));
    // 2.获取作用域及$compile服务实例,并在作用域中添加data模拟数据
    beforeEach(angular.mock.inject(function($rootScope, $compile) {
        mockScope = $rootScope.$new();
        compileService = $compile;
        mockScope.data = [
            { name: "Apples", category: "Fruit", price: 1.20, expiry: 10 },
            { name: "Bananas", category: "Fruit", price: 2.42, expiry: 7 },
            { name: "Pears", category: "Fruit", price: 2.02, expiry: 6 }];
    }));
    // 3.执行测试，并用jqLite的方式获取列表内容，然后对比
    it("Generates list elements", function () {

        var compileFn = compileService("<div unordered-list='data'></div>");
        var elem = compileFn(mockScope);

        expect(elem.children("ul").length).toEqual(1); 
        expect(elem.find("li").length).toEqual(3);
        expect(elem.find("li").eq(0).text()).toEqual("Apples");
        expect(elem.find("li").eq(1).text()).toEqual("Bananas");
        expect(elem.find("li").eq(2).text()).toEqual("Pears");
    });

});
```

#### 测试服务
由于通过inject方法获取服务很方便，所以测试服务很简单.
在app.js中添加一个简单的服务，该服务很简单，维护计数器并定义了两个方法，用于增加和返回计数。
```js
angular.module("exampleApp", [])
    .controller("defaultCtrl", function ($scope, $http, $interval, $timeout, $log) {

        $scope.intervalCounter = 0;
        $scope.timerCounter = 0;

        $interval(function () {
            $scope.intervalCounter++;
        }, 5, 10);

        $timeout(function () {
            $scope.timerCounter++;
        }, 5);

        $http.get("productData.json").success(function (data) {
            $scope.products = data;
            $log.log("There are " + data.length + " items");
        });

        $scope.counter = 0;

        $scope.incrementCounter = function () {
            $scope.counter++;
        }
    })
    .filter("labelCase", function () {
        return function (value, reverse) {
            if (angular.isString(value)) {
                var intermediate = reverse ? value.toUpperCase() : value.toLowerCase();
                return (reverse ? intermediate[0].toLowerCase() :
                    intermediate[0].toUpperCase()) + intermediate.substr(1);
            } else {
                return value;
            }
        };
    })
    .directive("unorderedList", function () {
        return function (scope, element, attrs) {
            var data = scope[attrs["unorderedList"]];
            if (angular.isArray(data)) {
                var listElem = angular.element("<ul>");
                element.append(listElem);
                for (var i = 0; i < data.length; i++) {
                    listElem.append(angular.element('<li>').text(data[i].name));
                }
            }
        }
    })
    .factory("counterService", function () {
        var counter = 0;
        return {
            incrementCounter: function () {
                counter++;
            },
            getCounter: function() {
                return counter;
            }
        }
    });
```

在tests/serviceTest.js中添加对服务的单元测试代码：
```js
describe("Service Tests", function () {

    beforeEach(angular.mock.module("exampleApp"));

    it("Increments the counter", function () {
        angular.mock.inject(function (counterService) {
            expect(counterService.getCounter()).toEqual(0);
            counterService.incrementCounter();
            expect(counterService.getCounter()).toEqual(1);
        });
    });
});
```