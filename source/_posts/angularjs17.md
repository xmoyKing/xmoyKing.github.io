---
title: angularjs入门笔记-17-自定义指令
categories:
  - fe
tags:
  - fe
  - angularjs
date: 2017-05-23 11:42:50
updated:
---

当内置指令无法满足需求时，就可以创建自定义指令，尤其是在需要能够用于多个ng程序的子包含的功能单元时。

使用Module.directive方法创建指令，参数为指令名和一个工厂函数。

要注意指令名是驼峰式命名`unorderedList`，而使用的时候是连字符的形式`unordered-list`：
```js
angular.module("exampleApp", [])
    .directive("unorderedList", function () {
        return function (scope, element, attrs) {
            // implementation code will go here
        }
    })
```
作为属性使用：
```html
<div unordered-list="products"></div>
```

### 链接函数
指令中工厂函数返回的工人函数即链接函数，它的作用是提供一些方法，这些方法将指令和html文档以及作用域连接起来。（实际上还有一个与指令相关联的函数叫编译函数）

当ng建立指令的实例时（指令的工厂函数其实是一个构造器，或说类模板，能够生成类的实例），链接函数被自动调用，同时传入三个参数，分别是：视图作用域、指令应用的html元素、以及html元素的属性，一般使用scope、element、attrs三个形参名来接收（注意scope没有$前缀，他们只是普通的js参数，不是通过依赖注入的，即参数的顺序是固定传入的）。


从作用域中获取数据的方式：从attrs集合中使用`unorderedList`作为key，然后传给scope对象获取作用域中的数据
```js
var data = scope[attrs["unorderedList"]];
```

生成html元素：element参数其实是一个剪裁后jquery对象，ng称为jqLite，也可以通过angularjs.element获取这个jqLite对象。
```js
function (scope, element, attrs) {
    var data = scope[attrs["unorderedList"]];
    if (angular.isArray(data)) {
        var listElem = angular.element("<ul>");
        element.append(listElem);
        for (var i = 0; i < data.length; i++) {
            listElem.append(angular.element('<li>').text(data[i].name));
        }
    }
}
```
上例的结果就是，在html元素上根据作用域中的数据生成一个列表。 

也可以在属性值中使用表达式，通过scope.$eval方法来计算。
```html
<div unordered-list="products" list-property="price | currency"></div>
```
```js
.directive("unorderedList", function () {
    return function (scope, element, attrs) {
        var data = scope[attrs["unorderedList"]];
        var propertyExpression = attrs["listProperty"];

        if (angular.isArray(data)) {
            var listElem = angular.element("<ul>");
            element.append(listElem);
            for (var i = 0; i < data.length; i++) {
                listElem.append(angular.element('<li>')
                    .text(scope.$eval(propertyExpression, data[i])));
            }
        }
    }
})
```

处理数据变化，可以通过$eval将表达式的值计算出来，然后通过jqLite写入html元素，但是却无法像内置指令那样在作用域数据变化时同步更新属性值表达式内的值。

要处理数据变化， 需要用到$watch方法来监控作用域中数据的变化，每当有变化时，就使用eval计算，然后再次通过jqLite写入到html中
```js
for (var i = 0; i < data.length; i++) {
    var itemElement = angular.element('<li>');
    listElem.append(itemElement);
    var watcherFn = function (watchScope) {
        return watchScope.$eval(propertyExpression, data[i]);
    }
    scope.$watch(watcherFn, function (newValue, oldValue) {
        itemElement.text(newValue);
    });
}
```
上述会产生闭包的问题，导致i的值越界。解决方法为使用IIFE,立即执行表达式，将i的值在当前循环时即确定传给$eval方法：
```js
for (var i = 0; i < data.length; i++) {
    (function () {
        var itemElement = angular.element('<li>');
        listElem.append(itemElement);
        var index = i;
        var watcherFn = function (watchScope) {
            return watchScope.$eval(propertyExpression, data[index]);
        }
        scope.$watch(watcherFn, function (newValue, oldValue) {
            itemElement.text(newValue);
        });
    }());
}
```

jqLite对DOM的支持与jquery有些许区别，比如children、eq、find、next、parent这几个命令。

attr和prop方法的区别：
prop方法处理的是被DOM API HTMLElement对象所定义的属性，而不是标记语言HTML元素定义的，通常是一样的，但是有一些属性是不一样的，比如class，它在HTMLElement对象中是用className驼峰式表示的。


### 使用选项自定义指令
ng提供了许多选项帮助开发，它们能够更加方便快捷的帮助开发复杂应用。

- restrict属性指定指令的使用方式
- template属性将内容使用HTML模版，而不是jqLite生成html元素
- templateUrl属性指定外部模版文件地址
- replace属性指定模版内容是否替换指令所在元素
- scope属性为true时为指令的每一个实例都创建一个隔离作用域
- 在隔离作用域能阻止指令继承父作用域
- @前缀表示在隔离作用域中创建一个单项绑定
- =前缀表示在隔离作用域中创建一个双向绑定
- &前缀表示在父作用域的上下文中计算一个表达式
- link属性指定链接函数
- compile属性指定编译函数
- transclude属性指定是否用于包含任意内容

ng默认情况下是将创建的指令当做属性使用，即restrict默认为`A`，表示属性.
restrict可以设置四种值：`E A C M`, 也可以单独使用，也可以混合使用:
- E 将指令作为元素使用
- A 将指令作为属性使用
- C 将指令作为css类使用
- M 将指令作为html注释使用

```js
.directive("unorderedList", function () {
    return {
        link: function (scope, element, attrs) {
            var data = scope[attrs["unorderedList"] || attrs["listSource"]];
            var propertyExpression = attrs["listProperty"] || "price | currency";
            if (angular.isArray(data)) {
                var listElem = angular.element("<ul>");
                if (element[0].nodeName == "#comment") {
                    element.parent().append(listElem);
                } else {
                    element.append(listElem);
                }
                for (var i = 0; i < data.length; i++) {
                    var itemElement = angular.element("<li>")
                        .text(scope.$eval(propertyExpression, data[i]));
                    listElem.append(itemElement);
                }
            }
        },
        restrict: "EACM"
    }
}
```
实际使用时，C和M的使用比较少见，一般是AE单独/混合使用。

当做元素使用：
```html
<unordered-list list-source="products" list-property="price | currency" />
```

当做css类的属性值使用：
```html
<div class="unordered-list: products" list-property="price | currency"></div>
```

当做注释使用：
```html
<!-- directive: unordered-list products  -->
```
在CM的情况下，若属性值比较多，还需要完成属性值的解析，同时需要修改link函数，以检查属性值及其数据来源。

#### link函数和compile函数区别
compile指定的编译函数应该只用于修改DOM，而link指定的链接函数只设置监听器或事件处理。
编译/链接分离有助于改善复杂指令的性能，但在项目中，一般只是用链接函数，因为编译函数只用来创建类似ng-repeat指令这样的功能。


#### 使用template模版
当使用template时，链接函数不需要负责生成展示数据的html元素了，而仅仅只需要将作用域内的数据准备好，完成监听器并将事件绑定做好即可。而模版负责生成html元素。

```html
<html ng-app="exampleApp">
<head>
    <title>Directives</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .directive("unorderedList", function () {
                return {
                    link: function (scope, element, attrs) {
                        scope.data = scope[attrs["unorderedList"]];
                    },
                    restrict: "A",
                    template: "<ul><li ng-repeat='item in data'>"
                        + "{{item.price | currency}}</li></ul>"
                }
            }).controller("defaultCtrl", function ($scope) {
                $scope.products = [
                    { name: "Apples", category: "Fruit", price: 1.20, expiry: 10 },
                    { name: "Bananas", category: "Fruit", price: 2.42, expiry: 7 },
                    { name: "Pears", category: "Fruit", price: 2.02, expiry: 6 }
                ];
            })
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3>Products</h3> 
        </div>
        <div class="panel-body">
            <div unordered-list="products">
                This is where the list will go
            </div>
        </div>
    </div>
</body>
</html> 
```

![template](1.png)

template也可以指定一个函数来表示生成模版，该函数调用时会被传入两个参数（分别表示html元素和其属性集合），同时该函数需要返回一个插入到文档中的html代码片段。

```html
<script type="text/template" id="listTemplate">
    <ul>
        <li ng-repeat="item in data">{{item.price | currency}}</li>
    </ul>
</script>
<script>
    angular.module("exampleApp", [])
        .directive("unorderedList", function () {
            return {
                link: function (scope, element, attrs) {
                    scope.data = scope[attrs["unorderedList"]];
                },
                restrict: "A",
                template: function () {
                    return angular.element(
                        document.querySelector("#listTemplate")).html();
                }
            }
        }).controller("defaultCtrl", function ($scope) {
            $scope.products = [
                { name: "Apples", category: "Fruit", price: 1.20, expiry: 10 },
                { name: "Bananas", category: "Fruit", price: 2.42, expiry: 7 },
                { name: "Pears", category: "Fruit", price: 2.02, expiry: 6 }
            ];
        })
</script>
```

templateUrl可以指定使用外部模版时的url地址，这个地址可以是字符串，