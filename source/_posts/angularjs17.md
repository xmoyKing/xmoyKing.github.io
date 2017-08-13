---
title: angularjs入门笔记-17-自定义指令1
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


