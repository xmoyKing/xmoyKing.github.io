---
title: angularjs入门笔记-13-元素和事件指令
categories:
  - angularjs
tags:
  - angularjs
date: 2017-05-18 08:41:45
updated:
---

学习用于在Dom中添加，删除，隐藏和显示元素的指令，以及从类中添加，删除元素并设置css样式属性，处理事件的指令，以及映射指令。

### 对元素设置

- ng-show和ng-hide， 显示或隐藏元素
- ng-if， 从dom删除元素 
- 带过滤器的ng-repeat， 在生成没有直接父元素的元素时避免嵌入包含问题
- ng-class和ng-style，将元素添加到css类中，或设置某个css属性
- ng-class-odd或ng-class-even， 对ng-repeat指令生成的奇数或偶数行添加不同的css类
- ng-click等事件指令， 定义某事件被触发时执行的行为
- 自定义事件指令， 处理ng未提供的指令事件
- ng-checked布尔属性指令， 对元素使用布尔属性

表格条纹化（隔行换色）问题以及ng-repeat冲突问题：
```html
<table class="table table-striped">
    <thead>
        <tr><th>#</th><th>Action</th><th>Done</th></tr>
    </thead>
    <tr ng-repeat="item in todos" ng-hide="item.complete">
        <td>{{$index + 1}}</td>
        <td>{{item.action}}</td>
        <td>{{item.complete}}</td>
    </tr>
</table>
```
```css
table.table.table-striped tr:nth-child(odd) td {
    background-color: moccasin;
}
```
![strip问题](1.png)

ng-show、ng-hide指令在应用到ng-repeat生成的表格元素的时候会有一些问题，正常情况下通过css设置条纹效果，由于将ng-hide用在tr上，而元素是被隐藏而不是移除，所以会出现条纹不一致。
似乎可以使用ng-if解决这个问题，但是ng-repeat也应用在tr上，所以会和ng-if冲突，因为两种指令都依赖ng称为嵌入包含的技术，所以会冲突，即ng-repeat和ng-if无法同时用在一个元素上。

这种情况下可以通过过滤器来解决：
```html
<tr ng-repeat="item in todos | filter: {complete: 'false'}">
```

使用ng-class和ng-style, 将元素添加到类中或设置css属性
```html
<html ng-app="exampleApp">
<head>
    <title>Directives</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .controller("defaultCtrl", function ($scope) {
                $scope.todos = [
                    { action: "Get groceries", complete: false },
                    { action: "Call plumber", complete: false },
                    { action: "Buy running shoes", complete: true },
                    { action: "Buy flowers", complete: false },
                    { action: "Call family", complete: false }];

                $scope.buttonNames = ["Red", "Green", "Blue"];

                $scope.settings = {
                    Rows: "Red", 
                    Columns: "Green"
                };
            });
    </script>
    <style>
        tr.Red { background-color: lightcoral; }
        tr.Green { background-color: lightgreen;}
        tr.Blue { background-color: lightblue; }
    </style>
</head>
<body>
    <div id="todoPanel" class="panel" ng-controller="defaultCtrl">
        <h3 class="panel-header">To Do List</h3>

        <div class="row well">
            <div class="col-xs-6" ng-repeat="(key, val) in settings">
                <h4>{{key}}</h4>
                <div class="radio" ng-repeat="button in buttonNames">
                    <label>
                        <input type="radio" ng-model="settings[key]"
                               value="{{button}}">{{button}}
                    </label>
                </div>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr><th>#</th><th>Action</th><th>Done</th></tr>
            </thead>
            <tr ng-repeat="item in todos" ng-class="settings.Rows">
                <td>{{$index + 1}}</td>
                <td>{{item.action}}</td>
                <td ng-style="{'background-color': settings.Columns}">
                    {{item.complete}}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
```
![ng-class和ng-style演示](2.png)

使用控制器中的简单对象的Rows设置表格的tr元素的背景色，使用columns设置完成Done一列的背景色，用repeat指令将这两个属性与两组单选框绑定。

如图，ng-class设置class,ng-style设置单个属性，一般来说还是建议使用class，但是由于ng中绑定元素，所以ng-style也是可以一次修改，全部生效的。

### 处理事件

在html元素上定义一些事件，提供与用户交互的功能。ng定义的一组指令，能够指定各种不同的事件。

- ng-change 为change事件指定响应，在表单元素内容状态改变时触发，如复选框、输入框
- ng-click 单击触发
- ng-dbclick 双击触发
- ng-copy, ng-cut, ng-paste 复制、剪切、粘贴时触发
- ng-focus 获取焦点时触发
- ng-blur 失去焦点时触发
- ng-keydown, ng-keypress, ng-keyup 按下、释放按键时触发
- ng-mousedown, ng-mouseenter, ng-mouseleave, ng-mouseover, ng-mouseup 鼠标与元素发生交互时触发
- ng-submit 当表单提交时触发

demo如下：
```html
<html ng-app="exampleApp">
<head>
    <title>Directives</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .controller("defaultCtrl", function ($scope) {

                $scope.todos = [
                    { action: "Get groceries", complete: false },
                    { action: "Call plumber", complete: false },
                    { action: "Buy running shoes", complete: true },
                    { action: "Buy flowers", complete: false },
                    { action: "Call family", complete: false }];

                $scope.buttonNames = ["Red", "Green", "Blue"];

                $scope.data = {
                    rowColor: "Blue",
                    columnColor: "Green"
                };

                $scope.handleEvent = function (e) {
                    console.log("Event type: " + e.type);
                    $scope.data.columnColor = e.type == "mouseover" ? "Green" : "Blue";
                }
            });
    </script>
    <style>
        .Red { background-color: lightcoral; }
        .Green { background-color: lightgreen; }
        .Blue { background-color: lightblue; }
    </style>
</head>
<body>
    <div id="todoPanel" class="panel" ng-controller="defaultCtrl">
        <h3 class="panel-header">To Do List</h3>

        <div class="well">
            <span ng-repeat="button in buttonNames">
                <button class="btn btn-info" ng-click="data.rowColor = button">
                    {{button}}
                </button>
            </span>
        </div>

        <table class="table">
            <thead>
                <tr><th>#</th><th>Action</th><th>Done</th></tr>
            </thead>
            <tr ng-repeat="item in todos" ng-class="data.rowColor"
                ng-mouseenter="handleEvent($event)"
                ng-mouseleave="handleEvent($event)">
                <td>{{$index + 1}}</td>
                <td>{{item.action}}</td>
                <td ng-class="data.columnColor">{{item.complete}}</td>
            </tr>
        </table>
    </div>
</body>
</html>
```
![ng-mouseenter](3.png)
点击表格上排按钮能更改行颜色，鼠标在右侧状态列移入/移出能修改列的颜色

通过$event将事件对象传入控制器绑定的函数，有的时候ng指令使用的事件名称与浏览器实现的事件名称可能不匹配，这种时候可用console.log打印出来测试一下。

ng的事件虽然依赖jQuery,但依然不能兼容到所有情况，所以实际使用时需多测试。

事件指令结合指令使用表达式对事件行为进行控制还是依赖控制器对行为进行控制？
这个问题，还得看具体使用，过渡依赖表达式和事件指令会创建出不好测试和维护的代码，在视图中可放一些简单的逻辑，其他复杂逻辑还是放在控制器中。

### 自定义指令
有的时候需要处理一些ng未提供内置指令支持的事件，这时就需要自定义指令了。如下示例自定义touchstart和touchend事件，在点击和释放触屏设备时触发，使用chrome中的移动设备模拟功能。

```html
<html ng-app="exampleApp">
<head>
    <title>Directives</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .controller("defaultCtrl", function ($scope, $location) {

                $scope.message = "Tap Me!";

            }).directive("tap", function () {
                return function (scope, elem, attrs) {
                    elem.on("touchstart touchend", function () {
                        scope.$apply(attrs["tap"]);
                    });
                }
            });
    </script>
</head>
<body>
    <div id="todoPanel" class="panel" ng-controller="defaultCtrl">
        <div class="well" tap="message = 'Tapped!'" style="font-size:50px">
            {{message}}
        </div>
    </div>
</body>
</html>
```
![自定义指令](4.png)

该demo使用directive方法创建一个tap指令，返回一个工厂函数，该函数处理指令所应用的元素，传给函数的参数分别为：指令应用的作用域，应用的元素（jqLite返回的jq对象），应用的元素的属性集合

使用jqLite的on方法为touchstart和touchend事件注册函数，在该函数内使用`scope.$apply`方法计算指令属性值，该属性值从属性集合中取到，即`message = 'Tapped!'`值。

### 布尔属性
大多数html的属性由其属性值的具体值确定，但某些html元素只要该属性存在即可产生效果，而不管值是什么。比如disabled属性，checked属性，即无法通过设置其值为false来取消该属性。

- ng-checked 在input元素上使用，对checked属性，
- ng-disabled 在input元素和button上使用，
- ng-open 在details元素上使用，
- ng-readonly 在input元素上使用
- ng-selected 在option元素上使用

```html
<html ng-app="exampleApp">
<head>
    <title>Directives</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .controller("defaultCtrl", function ($scope) {
                $scope.dataValue = false;
            });
    </script>
</head>
<body>
    <div id="todoPanel" class="panel" ng-controller="defaultCtrl">
        <h3 class="panel-header">To Do List</h3>

        <div class="checkbox well">
            <label>
                <input type="checkbox" ng-model="dataValue">
                Set the Data Value
            </label>
        </div>
            
        <button class="btn btn-success" ng-disabled="dataValue">My Button</button>
    </div>
</body>
</html>
```

![ng-disabled使用](5.png)

使用ng-model创建一个与dataValue属性的双向绑定，然后将这个属性应用到ng-disabled指令上。


### 其他属性
有一些属性是ng无法直接控制的属性

- ng-href a元素上href属性
- ng-src img元素上src属性
- ng-srcset img元素上srcset属性，H5的新属性,允许根据不同的大小和像素密度指定多个图片地址，目前支持不太好。

上述三个属性无法直接写入html元素下该原生属性中，即：
```html
<a href="{{data.asrc}}">本链接中绑定的模型属性在ng执行前无效</a>
<!-- 正确绑定链接的方式 -->
<a ng-href="{{data.asrc}}">本链接中绑定的地址，ng未执行时不显示链接而仅仅只是普通文本，ng执行时会自动替换原生href属性，</a>
```