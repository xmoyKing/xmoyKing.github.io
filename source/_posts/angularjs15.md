---
title: angularjs入门笔记-15-控制器和作用域
categories:
  - fe
tags:
  - fe
  - angularjs
date: 2017-05-24 22:27:04
updated:
---

了解作用域和控制器之间的关系，作用域作为一个沟通各控制器的桥梁，甚至可以与其他js框架集成。

主要涉及到如下内容：
- 创建控制器：使用Module.controller方法来定义控制器，并使用ng-controller指令将其绑定在html元素上
- 向控制器作用域添加数据和行为：在$scope服务上声明依赖，并在控制器的工厂方法中向其赋予相应的属性
- 创建单块控制器：将ng-controller指令应用于body元素，并使用工厂方法来定义应用程序所需要的数据和行为
- 重用控制器：将ng-controller指令应用在多个html元素上
- 在控制器之间进行通信：通过root scrope或者服务发送事件
- 从另一个控制器继承行为和数据：内嵌ng-controller指令
- 创建不带作用域的控制器
- 通知作用域某处发生变化：使用$apply、$watch和$watchCollection方法将变化注入给一个作用域或者监控一个作用域是否发生变化

### Why & When 作用域、控制器
控制器是模型与视图之间的纽带，给视图提供数据和服务，并定义所需的业务逻辑，从而将用户行为转换成模型上的变化。

- Why
没有控制器就无法构建ng程序，控制器通过作用域向视图提供数据和逻辑，从模型中暴露数据给视图，基于用户与视图的交互士模型产生变化所需的逻辑。
- When 控制器遍布整个应用


### 基本原理
controller方法创建控制器，参数是新建控制器的名字和一个将被用于创建控制器的函数，这个函数应该被理解为构造器，但也可认为是工厂函数，创建ng组件所需的许多方法调用通常被表示为使用一个函数（工厂函数）创建另外一个函数（工人函数）。

有的时候，可以再控制器中顶一个一个函数表达式，然后再绑定的html中调用并传入一个参数，这种用法很奇怪，为什么不直接简化该函数：
```html
<html ng-app="exampleApp">
<head>
    <title>Controllers</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .controller("simpleCtrl", function ($scope) {

                $scope.city = "London";

                $scope.getCountry = function (city) {
                    switch (city) {
                        case "London":
                            return "UK";
                        case "New York":
                            return "USA";
                    }
                }
            });
    </script>
</head>
<body>
    <div class="well" ng-controller="simpleCtrl">
        <p>The city is: {{city}}</p>
        <p>The country is: {{getCountry(city) || "Unknown"}}</p>
    </div>
</body>
</html>
```

原因是：
1. 意味着行为能够被任何city值所用，而不仅仅是被同一个作用域里定义的city值所用，再涉及到控制器继承时非常有用。
2. 因为接收参数能够使单元测试变得更简便一些，因为这种行为是自包含的，对控制器行为使用参数并不是必须的，而且即使不使用参数时也没有什么不好。

关于作用域，最重要的一点就是改动被传播出去，自动更新所有被依赖的数据值，即使这种数据更新是通过交互产生的。比如ng-model双向绑定的值。


### 组织控制器
使用一个单块的控制器，能够支持body元素的所有内容，对于小而简单的程序这是合理的，但当项目的复杂度增长时将会变得越来越笨拙不方便，尤其是需要包含局部视图时。

单块控制器：
通过在元素上使用ng-controller指令，使用一个应用与程序所有html元素的控制器。

这种方式的优点：简单、无需担心各个作用域之间的通信问题，而且行为将被整个html所用，当使用一个单块控制器时，其实会对整个应用创建一个单独的视图。
```
controller -> scope -> view
```

缺点：用于简单情况，但若不断添加行为后，最终最得到一个臃肿的应用。与ng的设计哲学相违背，应该构建一个小而内聚的积木式模块，但这仅仅是风格问题，不是技术必须的。

### 复用控制器
可以在一个应用中创建多个视图并复用同一个控制器，ng会将每一个控制器的工厂函数单独调用，结果是每个控制器实例将会拥有自己的作用域。这将分离职能，不同的视图能以不同的方法对同一份数据和功能进行展示。
```html
<html ng-app="exampleApp">
<head>
    <title>Controllers</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .controller("simpleCtrl", function ($scope) {

                $scope.setAddress = function (type, zip) {
                    console.log("Type: " + type + " " + zip);
                }

                $scope.copyAddress = function () {
                    $scope.shippingZip = $scope.billingZip;
                }
            });
    </script>
</head>
<body>
    <div class="well" ng-controller="simpleCtrl">
        <h4>Billing Zip Code</h4>
        <div class="form-group">
            <input class="form-control" ng-model="zip">
        </div>
        <button class="btn btn-primary" ng-click="setAddress('billingZip', zip)">
            Save Billing
        </button>
    </div>
    <div class="well" ng-controller="simpleCtrl">
        <h4>Shipping Zip Code</h4>
        <div class="form-group">
            <input class="form-control" ng-model="zip">
        </div>
        <button class="btn btn-primary" ng-click="copyAddress()">
            Use Billing
        </button>
        <button class="btn btn-primary" ng-click="setAddress('shippingZip', zip)">
            Save Shipping
        </button>
    </div>
</body>
</html>
```

在上述的demo中，每个控制器向其作用域提供的数据和行为都与另外一个控制器相互独立，每个控制器只关心收集单独一个邮编，能简化控制器和视图。

但这种分开的控制器的副作用就是，copyAddress无法使用，因为变量被保存在了不用的作用域中。这时就必须用到ng提供的作用域之间共享数据的机制。

### 作用域之间的通信
作用域实际上是一个层级结构，顶层是根作用域(root scope),每个控制器都被赋予一个新的作用域，整个作用域是根作用域的一个子作用域。

![作用域组织](1.png)

根作用域提供了各个作用域之间发送事件的方法，通这个方法即可以在各个控制器之间通信。

```html
<html ng-app="exampleApp">
<head>
    <title>Controllers</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .controller("simpleCtrl", function ($scope, $rootScope) {

                $scope.$on("zipCodeUpdated", function (event, args) {
                    $scope[args.type] = args.zipCode;
                });

                $scope.setAddress = function (type, zip) {
                    $rootScope.$broadcast("zipCodeUpdated", {
                        type: type, zipCode: zip 
                    });
                    console.log("Type: " + type + " " + zip);
                }

                $scope.copyAddress = function () {
                    $scope.zip = $scope.billingZip;
                }
            });
    </script>

</head>
<body>
    <div class="well" ng-controller="simpleCtrl">
        <h4>Billing Zip Code</h4>
        <div class="form-group">
            <input class="form-control" ng-model="zip">
        </div>
        <button class="btn btn-primary" ng-click="setAddress('billingZip', zip)">
            Save Billing
        </button>
    </div>
    <div class="well" ng-controller="simpleCtrl">
        <h4>Shipping Zip Code</h4>
        <div class="form-group">
            <input class="form-control" ng-model="zip">
        </div>
        <button class="btn btn-primary" ng-click="copyAddress()">
            Use Billing
        </button>
        <button class="btn btn-primary" ng-click="setAddress('shippingZip', zip)">
            Save Shipping
        </button>
    </div>
</body>
</html>
```

根作用域可以作为一个服务被使用，所以在控制器中使用$rootScope名称声明对它的依赖，所有的作用域，包括$rootScope服务，定义了若干可用于发送和接收事件的方法。

- $broadcast(name, args) 向当前作用域下的所有子作用域发送一个事件，参数是事件名称以及一个用于向事件提供额外数据的对象
- $emit(name, args) 向当前作用域的父作用域发送一个事件，直至根作用域。
- $on(name, handler) 注册一个事件处理函数，该函数在特定的事件被当前作用域收到时将会被调用

$broadcast和$emit事件都是具有方向性的，他们沿作用域的层级结构向上发送事件直至根作用域，或向下发送直至每一个子作用域。

在上例中，当前作用域中调用$on方法，用来对zipCodeUpdated事件创建一个处理函数，这个函数接收一个Event对象以及一个参数对象（本例中为一个type和zipCode对象）

### 使用服务调解作用域事件
ng一般使用服务来调解作用域之间的通信，这种方法可减少重复，使用Module.service方法创建一个服务对象，该服务可被控制器用来发送和接收事件，而无需直接与作用域中的事件方法产生交互。

```html
<html ng-app="exampleApp">
<head>
    <title>Controllers</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .service("ZipCodes", function($rootScope) {
                return {
                    setZipCode: function(type, zip) {
                        this[type] = zip;
                        $rootScope.$broadcast("zipCodeUpdated", {
                            type: type, zipCode: zip 
                        });
                    }
                }
            })
            .controller("simpleCtrl", function ($scope, ZipCodes) {

                $scope.$on("zipCodeUpdated", function (event, args) {
                    $scope[args.type] = args.zipCode;
                });

                $scope.setAddress = function (type, zip) {
                    ZipCodes.setZipCode(type, zip);
                    console.log("Type: " + type + " " + zip);
                }

                $scope.copyAddress = function () {
                    $scope.zip = $scope.billingZip;
                }
            });
    </script>


</head>
<body>
    <div class="well" ng-controller="simpleCtrl">
        <h4>Billing Zip Code</h4>
        <div class="form-group">
            <input class="form-control" ng-model="zip">
        </div>
        <button class="btn btn-primary" ng-click="setAddress('billingZip', zip)">
            Save Billing
        </button>
    </div>
    <div class="well" ng-controller="simpleCtrl">
        <h4>Shipping Zip Code</h4>
        <div class="form-group">
            <input class="form-control" ng-model="zip">
        </div>
        <button class="btn btn-primary" ng-click="copyAddress()">
            Use Billing
        </button>
        <button class="btn btn-primary" ng-click="setAddress('shippingZip', zip)">
            Save Shipping
        </button>
    </div>
</body>
</html>
```

控制器能够继承，即通过ng-controller指令的嵌入位置，可以让一个父控制器中定义的功能在子控制器中使用。


<p data-height="265" data-theme-id="0" data-slug-hash="PKKjZe" data-default-tab="html,result" data-user="xmoyking" data-embed-version="2" data-pen-title="controller&scope" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/PKKjZe/">controller&scope</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>

代码如下：
```html
<html ng-app="exampleApp">
<head>
    <title>Controllers</title>
    <script src="angular.js"></script>
    <script src="controllers.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
</head>
<body ng-controller="topLevelCtrl">

    <div class="well">
        <h4>Top Level Controller</h4>
        <div class="input-group">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" 
                        ng-click="reverseText()">Reverse</button>
                <button class="btn btn-default" type="button"
                        ng-click="changeCase()">Case</button>
            </span>
            <input class="form-control" ng-model="dataValue">
        </div>
    </div>

    <div class="well" ng-controller="firstChildCtrl">
        <h4>First Child Controller</h4>
        <div class="input-group">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"
                        ng-click="reverseText()">Reverse</button>
                <button class="btn btn-default" type="button"
                        ng-click="changeCase()">Case</button>
            </span>
            <input class="form-control" ng-model="dataValue">
        </div>
    </div>    

    <div class="well" ng-controller="secondChildCtrl">
        <h4>Second Child Controller</h4>
        <div class="input-group">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"
                        ng-click="reverseText()">Reverse</button>
                <button class="btn btn-default" type="button"
                        ng-click="changeCase()">Case</button>
                <button class="btn btn-default" type="button"
                        ng-click="shiftFour()">Shift</button>
            </span>
            <input class="form-control" ng-model="dataValue">
        </div>
    </div>    
</body>
</html>
```

```js
var app = angular.module("exampleApp", []);

app.controller("topLevelCtrl", function ($scope) {

    $scope.dataValue = "Hello, Adam";

    $scope.reverseText = function () {
        $scope.dataValue = $scope.dataValue.split("").reverse().join("");
    }

    $scope.changeCase = function () {
        var result = [];
        angular.forEach($scope.dataValue.split(""), function (char, index) {
            result.push(index % 2 == 1
                ? char.toString().toUpperCase() : char.toString().toLowerCase());
        });
        $scope.dataValue = result.join("");
    };
});

app.controller("firstChildCtrl", function ($scope) {

    $scope.changeCase = function () {
       $scope.dataValue = $scope.dataValue.toUpperCase();
    };
});

app.controller("secondChildCtrl", function ($scope) {

    $scope.changeCase = function () {
       $scope.dataValue = $scope.dataValue.toLowerCase();
    };

    $scope.shiftFour = function () {
        var result = [];
        angular.forEach($scope.dataValue.split(""), function (char, index) {
            result.push(index < 4 ? char.toUpperCase() : char);
        });
        $scope.dataValue = result.join("");
    }
});
```


通过上例可看到，三个控制器都提供Reverse按钮用于反转输入框元素字符顺序，当通过ng-controller指令将控制器嵌入另一个控制器中时，子控制器的作用域便继承父控制器作用域中的数据和行为。

这些输入框元素都被连接到dataValue属性上，都调用reverseText方法，由于属性和方法都是都顶层控制器中被定义，即使在子控制器中单击reverse按钮，顶层输入框也会改变。

![子控制器](2.png)

覆盖和扩展继承的数据和行为：
由于子控制器能够覆盖他们父控制机中的数据和行为，即数据和方法名能被同名的局部数据和行为覆盖，可以看到每个子控制器都在自己的作用域定义了名为changeCase的行为，这些行为的实现不同，所以，点击调用时的反馈就不一样。

这种覆盖和扩展机制是符合js本身的语言特性的，这允许只改写需要自定义的部分，为不同部分定制控制器。

数据继承的问题：
这个demo显示了一种独特的情况(bug?)，若只修改第一个top level中的输入框，则所有输入框都会更新，同是点击三个reverse的结果都是一样的：三个输入框都会改变。

但是若单独修改了第二个或第三个输入框，则会发现reverse“失效”了，只能控制第一个输入框的值了。

即，被编辑后的输入框似乎独立了，一个同名变量名覆盖了父作用域中的变量（其实是由于js的原型继承）。

### 无作用域的控制器
若一个应用不需要继承，也不需要在控制器之间通信，则可选择使用无作用域的控制器，而取代作用域为视图提供数据和行为的是一个特殊变量，该变量代表了控制器本身。
```html
<html ng-app="exampleApp">
<head>
    <title>Controllers</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        var app = angular.module("exampleApp", [])
            .controller("simpleCtrl", function () {
                this.dataValue = "Hello, Adam";

                this.reverseText = function () {
                    this.dataValue = this.dataValue.split("").reverse().join("");
                }
            });
    </script>
</head>
<body>
    <div class="well" ng-controller="simpleCtrl as ctrl">
        <h4>Top Level Controller</h4>
        <div class="input-group">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"
                        ng-click="ctrl.reverseText()">Reverse</button>
            </span>
            <input class="form-control" ng-model="ctrl.dataValue">
        </div>
    </div>
</body>
</html>
```
上例的控制器没有声明对$scope的依赖，而是通过js的关键字this定义了自己的数据和行为。

当使用无作用域的控制器时，ng-controller指令的表达式又一些不同，需要指定一个代表控制器的变量名（上例为ctrl）


### 显示更新作用域
有的时候需要显示更新作用域，例如将ng与其他js框架集成使用时，ng提供了一些方法能够注册响应作用域上变化的函数，以及从ng代码之外向改变作用域内的数据。
- $apply(expression) 更新作用域
- $watch(expression, handler) 注册一个函数，当expression表达式所引用的值变化时，该函数会被调用
- $watchCollection(object, handler) 注册一个函数，当object对象的任何属性变化时，该函数会被调用

```html
<html ng-app="exampleApp">
<head>
    <title>Controllers</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js">
        </script>
    <link rel="stylesheet" href=
   "http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/sunny/jquery-ui.min.css">
    <script>
        $(document).ready(function () {
            $('#jqui button').button().click(function (e) {
                angular.element(angularRegion).scope().$apply('handleClick()');
            });
        });



        var app = angular.module("exampleApp", [])
            .controller("simpleCtrl", function ($scope) {

                $scope.buttonEnabled = true;
                $scope.clickCounter = 0;

                $scope.handleClick = function () {
                    $scope.clickCounter++;
                }

                $scope.$watch('buttonEnabled', function (newValue) {
                    $('#jqui button').button({
                        disabled: !newValue
                    });
                });
            });
    </script>

</head>
<body>
    <div id="angularRegion" class="well" ng-controller="simpleCtrl">            
        <h4>AngularJS</h4>
        <div class="checkbox">
            <label>
                <input type="checkbox" ng-model="buttonEnabled"> Enable Button
            </label>
        </div>
        Click counter: {{clickCounter}}
    </div>
    <div id="jqui" class="well">
        <h4>jQuery UI</h4>
        <button>Click Me!</button>
    </div>
</body>
</html>
```

<p data-height="265" data-theme-id="0" data-slug-hash="wqqqGv" data-default-tab="result" data-user="xmoyking" data-embed-version="2" data-pen-title="显式更新作用域" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/wqqqGv/">显式更新作用域</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>

$watch方法提供对外集成的手段，作用域上某个变化可以出发调用其他框架中响应变化，注册了对buttonEnabled属性的监听，在其内使用了jq的方式更新元素的状态。

而在jQuery的作用域内，需要通过ng找到控制器元素所关联的作用域，然后才能使用ng中控制器定义的方法，通过$apply来调用handleClick(注意这里传入的是一个表达式字符串)，然后修改clickCounter变量值，也可以通过一个修改clickCounter变量的表达式来达到目的。
```js
angular.element(angularRegion).scope().$apply('clickCounter += 1');
```
但使用方法比较好，因为这样能保持调用逻辑的统一和方便维护。
