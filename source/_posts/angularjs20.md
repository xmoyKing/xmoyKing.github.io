---
title: angularjs入门笔记-20-针对错误、表达式、全局对象的服务
categories:
  - fe
tags:
  - fe
  - angularjs
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
当show的参数为bottom时，调用$anchorScroll服务，否则不调用。具体表现为：浏览器能在单击Go to Bottom按钮时滚动到底部，但是单击Go to Top按钮时不滚动到顶部。

#### $log服务
ng提供的$log服务是对全局console对象的封装，它能使单元测试更容易,下面的代码将$log用于使用工厂方法定义的服务
```js
angular.module("customServices", [])
    .factory("logService", function ($log) {
        var messageCount = 0;
        return {
            log: function (msg) {
                $log.log("(LOG + " + this.messageCount++ + ") " + msg);
            }
        };
    });
```
$log服务的默认行为不是调用debug方法到控制台，可通过设置$logProvider.debugEnabled属性为true启用调试。

### 异常
ng使用$exceptionHandler服务处理应用出现的异常，默认的实现是调用$log服务定义的error方法，在其中调用全局console.error方法。
异常分为两大类，第一类是在编码和测试期间产生的，这个是自然开发周期的一部分，能够帮助应用开发，使其更健壮。第二类是应用发布之后公众看到的。
处理异常的方法不同，但捕获这些异常是应该的，因为这样可以响应异常，并为未来的分析做记录。这就是为什么需要$exceptionHandler服务出现的原因。
$exceptionHandler服务仅处理未捕获的异常，使用js原生的try...catch捕获的异常将不会被服务处理。
```html
<html ng-app="exampleApp">
<head>
    <title>Exceptions</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope) {
            $scope.throwEx = function () {
                throw new Error("Triggered Exception");
            }
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="panel panel-default">
        <div class="panel-body">
            <button class="btn btn-primary" ng-click="throwEx()">Throw Exception</button>
        </div>
    </div>
</body>
</html>
```
点击Throw Exception按钮后能在控制台看到报错信息，并能看到一堆的记录。
![抛出异常](1.png)

也可以显式使用$exceptionHandler服务，该服务对象有两个参数，异常和用于描述异常的原因的可选字符串。
```js
.controller("defaultCtrl", function ($scope, $exceptionHandler) {
    $scope.throwEx = function () {
        try {
            throw new Error("Triggered Exception");
        } catch (ex) {
            $exceptionHandler(ex.message, "Button Click");
        }
    }
});
```
![$exceptionHandler显式使用](2.png)

还可以自定义服务来覆盖ng默认的异常处理服务，这样做能更好的格式化异常以及显示，但是需慎重，因为异常处理的代码需要经过严格测试，无懈可击，否则会让代码难以调试和维护。
```js
angular.module("exampleApp", [])
.controller("defaultCtrl", function ($scope, $exceptionHandler) {
    $scope.throwEx = function () {
        try {
            throw new Error("Triggered Exception");
        } catch (ex) {
            $exceptionHandler(ex, "Button Click");
        }
    }
})
.factory("$exceptionHandler", function ($log) {
    return function (exception, cause) {
        $log.error("Message: " + exception.message + " (Cause: " + cause + ")");
    }
});
```
![自定义服务覆盖$exceptionHandler](3.png)

### 处理危险数据
在Web安全中，常见的攻击手段为xss（跨站脚本注入）和csrf(跨域请求伪造)，这些攻击手段将恶意内容通过表单注入到应用中，而这些危险内容会回显给其他浏览该恶意代码页面的用户，以达到攻击目的。
ng内置一些安全服务处理这些危险数据，降低风险，如sce(strict contextual escaping, 移除危险元素和属性), sanitize（替换危险字符并转义）。
sce服务是默认开启的，预防不安全的值通过数据绑定被展示到页面上。
```html
<html ng-app="exampleApp">
<head>
    <title>SCE</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope) {
            $scope.htmlData 
                = "<p>This is <b onmouseover=alert('Attack!')>dangerous</b> data</p>";            
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
        <p><input class="form-control" ng-model="htmlData" /></p>
        <p>{{htmlData}}</p>
    </div>
</body>
</html>
```
上述代码就是在一个输入框获取输入并显示到界面，若成功，则会在鼠标移到dangerous字符上时弹出提示框，但由于sce，ng将危险的字符（HTML中的`<`和`>`字符）转义替换，所以直接显示了代码并且其中的交互和js脚本无法执行。
![sce转义](4.png)

有的时候需求中确实需要显示执行一些代码或者交互，此时可使用ng-bind-html指令，它信任所指定的数据，所以不会转义左右尖括号而直接显示，ng-bind-html指令依赖ngSanitize模块，可能需要独立下载这个模块并提前载入。

```html
<html ng-app="exampleApp">
<head>
    <title>SCE</title>
    <script src="angular.js"></script>
    <script src="angular-sanitize.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", ["ngSanitize"])
        .controller("defaultCtrl", function ($scope) {
            $scope.htmlData
                = "<p>This is <b onmouseover=alert('Attack!')>dangerous</b> data</p>";
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
        <p><input class="form-control" ng-model="htmlData" /></p>
        <p ng-bind-html="htmlData"></p>
    </div>
</body>
</html>
```
![ng-bind-html](5.png)

代码运行如上图，但却发现onmouseover没能起作用，打开控制台，发现html代码中的`onmouseover=alert('Attack!')`被移除了。其实是因为ngSanitize模块的$sanitize服务被默认用于ng-bind-html指令，这个服务能提出script和css元素、内联js事件处理器和样式属性以及可能造成问题的任何字符（即sanitize净化）。

$sanitize服务也可以被显式使用，净化数据。
```html
<html ng-app="exampleApp">
<head>
    <title>SCE</title>
    <script src="angular.js"></script>
    <script src="angular-sanitize.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", ["ngSanitize"])
        .controller("defaultCtrl", function ($scope, $sanitize) {
            $scope.dangerousData
                = "<p>This is <b onmouseover=alert('Attack!')>dangerous</b> data</p>";

            $scope.$watch("dangerousData", function (newValue) {
                $scope.htmlData = $sanitize(newValue);
            });
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
        <p><input class="form-control" ng-model="dangerousData" /></p>
        <p ng-bind="htmlData"></p>
    </div>
</body>
</html>
```
上述代码在input元素上绑定ng-model指令，在控制器中用监听器监听绑定属性改变，当有新值时使用$sanitize服务净化对象，剔除事件处理器，但ng-bind仍然会转义危险的左右尖括号。效果如下，
![显式使用$sanitize服务](6.png)

在极少的情况下，可能需要显示原始数据（不转义，不净化），此时使用$sce服务的trustAsHtml方法指定内容可信，同时使用ng-bind-html防止被ng默认转义。
```html
<html ng-app="exampleApp">
<head>
    <title>SCE</title>
    <script src="angular.js"></script>
    <script src="angular-sanitize.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", ["ngSanitize"])
        .controller("defaultCtrl", function ($scope, $sce) {
            $scope.htmlData
                = "<p>This is <b onmouseover=alert('Attack!')>dangerous</b> data</p>";

            $scope.$watch("htmlData", function (newValue) {
                $scope.trustedData = $sce.trustAsHtml(newValue);
            });
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
        <p><input class="form-control" ng-model="htmlData" /></p>
        <p ng-bind-html="trustedData"></p>
    </div>
</body>
</html>
```
![$sce.trustAsHtml](7.png)


### 表达式和指令
ng提供了一些用于处理内容和绑定表达式的服务，这些服务将内容处理为函数，包括简单表达式，html片段，绑定和指令
- $compile 将包含绑定和指令的html片段转换为被调用的函数生成内容
- $interpolate 将包含内联绑定的字符串转换为能被调用的函数生成内容
- $parse 将ng表达式转换为能被调用的函数生成内容
上述服务的目的在于控制用于生成和显示内容的过程，在基础指令中不需要使用这些服务，但当需要精确管理模版时，他们就非常有用了。

### 表达式转函数
$parse服务传入ng表达式，并转换表达式为函数，然后使用该函数求得表达式的值（具体的值与其所处的作用域有关）
```html
<html ng-app="exampleApp">
<head>
    <title>Expressions</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope) {
            $scope.price = "100.23";
        })
        .directive("evalExpression", function ($parse) {
            return function(scope, element, attrs) {
                scope.$watch(attrs["evalExpression"], function (newValue) {
                    try {
                        var expressionFn = $parse(scope.expr);
                        var result = expressionFn(scope);
                        if (result == undefined) {
                            result = "No result";
                        }
                    } catch (err) {
                        result = "Cannot evaluate expression";
                    }
                    element.text(result);
                });
            }
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
        <p><input class="form-control" ng-model="expr" /></p>
        <div>
            Result: <span eval-expression="expr"></span>
        </div>
    </div>
</body>
</html>
```
![表达式转为函数](8.png)
上述示例创建了叫evalExpression的指令，在span元素上应用指令并使用expr属性配置，当绑定的expr值为一个ng表达式时，将表达式的结果计算并显示在span文本中。
其中$parse服务的使用很简单，它是一个函数，参数是被求值的表达式，但$parse服务不计算表达式本身，它是一个工厂函数，返回实际执行工作的工人函数的工厂函数, 而返回的结果也是一个函数，此时将作用域传入该返回函数即可计算表达式字符串了。
```js
var expressionFn = $parse(scope.expr);
var result = expressionFn(scope);
```

也可以使用本地数据给表达式转换后的函数：
```html
<html ng-app="exampleApp">
<head>
    <title>Expressions</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope) {
            $scope.dataValue = "100.23";
        })
        .directive("evalExpression", function ($parse) {
            var expressionFn = $parse("total | currency");
            return {
                scope: {
                    amount: "=amount",
                    tax: "=tax"
                },
                link: function (scope, element, attrs) {
                    scope.$watch("amount", function (newValue) {
                        var localData = {
                            total: Number(newValue) 
                               + (Number(newValue) * (Number(scope.tax) /100))
                        }
                        element.text(expressionFn(scope, localData));
                    });
                }
            }            
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
        <p><input class="form-control" ng-model="dataValue" /></p>
        <div>
            Result: <span eval-expression amount="dataValue" tax="10"></span>
        </div>
    </div>
</body>
</html>
```
上例默认为：`Result: $110.25`


#### 插入字符串
$interpolate服务和它的提供器$interpolateProvider，用于配置ng执行内插的方式，将表达式插入字符串的过程，$interpolate服务比$parse更灵活，它能和包含表达式的字符串一起工作，而不仅仅是表达式。
```html
<html ng-app="exampleApp">
<head>
    <title>Expressions</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope) {
            $scope.dataValue = "100.23";
        })
        .directive("evalExpression", function ($interpolate) {
            var interpolationFn
                = $interpolate("The total is: {{amount | currency}} (including tax)");
            return {
                scope: {
                    amount: "=amount",
                    tax: "=tax"
                },
                link: function (scope, element, attrs) {
                    scope.$watch("amount", function (newValue) {
                        var localData = {
                            total: Number(newValue) 
                                + (Number(newValue) * (Number(scope.tax) /100))
                        }
                        element.text(interpolationFn(scope));
                    });
                }
            }            
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
        <p><input class="form-control" ng-model="dataValue" /></p>
        <div>
            <span eval-expression amount="dataValue" tax="10"></span>
        </div>
    </div>
</body>
</html>
```
![interpolate](9.png)
使用$interpolate服务比使用$parse简单，但有明显的差异，最明显的是$interpolate服务能操作非ng内容和内联绑定混合的字符串。第二个不同的就是，无法提供作用域和本地数据给$interpolate服务创建的内插函数，因为必须确保白哦大师所需的数值被包含在传入内插函数的对象中。
```js
{{ }} //这两个表示内联绑定的字符，双写大括号，称为内插字符
```
内插字符能够通过$interpolate服务的提供器$interpolateProvider改变，
- startSymbol(symbol) 替换起始字符，默认为双写{
- endSymbol(symbol) 替换结束字符，默认为双写}
```js
angular.module("exampleApp", [])
.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol("!!");
    $interpolateProvider.endSymbol("!!");
})
// 指令中使用
var interpolationFn
    = $interpolate("The total is: !!amount | currency!! (including tax)");
// html中绑定
<p>Original amount: !!dataValue!!</p>
```

### 编译内容
$compile服务处理包含绑定与表达式的html片段，它能创建可利用作用域生成内容的函数，相当于$parse和$interpolate，但不支持指令。使用$compile服务比其他服务要复杂一些。
```html
<html ng-app="exampleApp">
<head>
    <title>Expressions</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope) {
            $scope.cities = ["London", "Paris", "New York"];
        })
        .directive("evalExpression", function($compile) {
            return function (scope, element, attrs) {
                var content = "<ul><li ng-repeat='city in cities'>{{city}}</li></ul>"
                var listElem = angular.element(content);
                var compileFn = $compile(listElem);
                compileFn(scope);
                element.append(listElem);
            }
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well">
        <span eval-expression></span>
    </div>
</body>
</html>
```
指令使用$compile服务处理html片段，该片段使用ng-repeat指令并和城市数据一起填入ul中，注意`comileFn(scope)`并没有返回值，而是在原来的listElem上计算并更新，然后直接将元素添加到DOM中即可。