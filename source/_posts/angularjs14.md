---
title: angularjs入门笔记-14-表单
categories:
  - fe
tags:
  - fe
  - angularjs
date: 2017-05-18 11:07:57
updated:
---

绝大多数Web应用必不可少的就是表单了，ng对表单提供了从数据绑定到验证的不同程度的支持，提供一些额外的属性对表单增强。

- form元素的novalidate属性，使用特殊变量($valid)可得到某个元素或整个表单的有效性。
- 提供校验结果的视觉提示，使用ng校验器的css类
- 延迟校验反馈，可添加锁住校验反馈的变量
- 对input元素执行复杂校验
- 使用ng-true-value和ng-false-value属性，通过复选框绑定控制模型属性值
- 校验textarea元素
- 通过ng-option对select元素生成option选项

除了创建控制器时，对显示定义的模型属性进行操作，通过双向数据绑定会隐式的在数据模型中创建新的对象或属性，这种特性在使用表单元素收集信息时非常有用。

### 添加新对象到模型
如下demo就使用隐式创建的方式创建新对象到模型中。

```html
<html ng-app="exampleApp">
<head>
    <title>Forms</title>
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

                $scope.addNewItem = function (newItem) {
                    $scope.todos.push({
                        action: newItem.action + " (" + newItem.location + ")",
                        complete: false
                    });
                };
            });
    </script>
</head>
<body>
    <div id="todoPanel" class="panel" ng-controller="defaultCtrl">

        <h3 class="panel-header">
            To Do List
            <span class="label label-info">
                {{ (todos | filter: {complete: 'false'}).length}}
            </span>
        </h3>

        <div class="row">
            <div class="col-xs-6">
                <div class="well">
                    <div class="form-group row">
                        <label for="actionText">Action:</label>
                        <input id="actionText" class="form-control"
                               ng-model="newTodo.action">
                    </div>
                    <div class="form-group row">
                        <label for="actionLocation">Location:</label>
                        <select id="actionLocation" class="form-control"
                                ng-model="newTodo.location">
                            <option>Home</option>
                            <option>Office</option>
                            <option>Mall</option>
                        </select>
                    </div>
                    <button class="btn btn-primary btn-block"
                            ng-click="addNewItem(newTodo)">
                        Add
                    </button>
                </div>
            </div>

            <div class="col-xs-6">
                <table class="table">
                    <thead>
                        <tr><th>#</th><th>Action</th><th>Done</th></tr>
                    </thead>
                    <tr ng-repeat="item in todos">
                        <td>{{$index + 1}}</td>
                        <td>{{item.action}}</td>
                        <td>
                            <input type="checkbox" ng-model="item.complete">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
```
![通过表单在模型中创建新对象](1.png)

在上述示例中在input上绑定了一个newTodo.action，在select上绑定newTodo.location，然后在button上绑定点击事件，将newTodo对象传入addNewItem函数中。

在控制器中的addNewItem函数，虽然可以通过直接访问$scope.newTodo实现添加新任务，而不是接收一个对象作为参数，看似多此一举，但实际上，接收对象参数的方法能在视图中多次复用，尤其是在需要考虑到控制器的继承关系时非常重要。

在页面被初次加载时，newTodo对象以及action和location属性并不存在，模型中仅有的数据是已有的待办事项，这些数据是控制器函数中硬编码写死的。

而在input元素或selcet元素改变时，ng将自动创建newTodo对象并能更新这个对象，ng这种灵活创建新对象和属性的方式非常有利于快速开发并修改模型，以简洁的方式调用并处理数据。

但这种隐式创建对象的方式也有问题，比如若一开始没有输入也没有选择的时候直接点击add按钮，会报错：
```js
TypeError: Cannot read property 'action' of undefined
```
这种问题就是由于控制器中的方法函数视图访问一个ng尚未创建出的对象的属性导致的，而对象只有在（即输入框、选择框被改变后）触发双向绑定指令后才能被创建，考虑到这种情况（编写程序时很大可能不会考虑到），需要对方法参数进行检查。
```js
$scope.addNewItem = function (newItem) {
    if (angular.isDefined(newItem) && angular.isDefined(newItem.action)
            && angular.isDefined(newItem.location)) {

        $scope.todos.push({
            action: newItem.action + " (" + newItem.location + ")",
            complete: false
        });
    }
};
```

其实更好的方法是对表单进行校验，尤其是程序在使用用户输入的数据之前需要进行检查。这里需要提到一些用户交互和体验以及程序功能上的平衡或取舍问题。

### 为什么用户会输入错误的数据？
数据输入不合理，很多时候其实算是开发者的锅，很多数据不规则的问题在一定程度上可通过细致的设计和开发来避免。
1. 用户不理解要求输入的是什么，这种情况往往是由于提示不够明确，或仅仅是用户没有注意到。为了减少用户的混淆和疏忽，可采取一些方法，比如尽可能早的要求填写那些必要的信息，同时组织表单减少混淆，让标签语义更清晰，遵循一些表单元素的惯用顺序等。
2. 用户不想提供要求的数据，比如一些用户想尽快完成填表，输入尽量少的数据，或者就不愿提供精确的数据，比如一些私人信息。
3. 用户没有该要求的数据，比如地区，有一些地区分为三级，但是该用户所在地区只有两级，没有第三级。
4. 仅仅是简简单单的用户输入错误，这种情况其实最常见，只要是需要输入的地方，都有可能输入错误数据，这种情况应该考虑如何有效减少用户输入而不是处理这种错误。

如下验证表单的示例：
```html
<html ng-app="exampleApp">
<head>
    <title>Forms</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .controller("defaultCtrl", function ($scope) {
                $scope.addUser = function (userDetails) {
                    $scope.message = userDetails.name
                        + " (" + userDetails.email + ") (" + userDetails.agreed + ")";
                }

                $scope.message = "Ready";
            });
    </script>
</head>
<body>
    <div id="todoPanel" class="panel" ng-controller="defaultCtrl">
        <form name="myForm" novalidate ng-submit="addUser(newUser)">
            <div class="well">
                <div class="form-group">
                    <label>Name:</label>
                    <input name="userName" type="text" class="form-control"
                           required ng-model="newUser.name">
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input name="userEmail" type="email" class="form-control"
                           required ng-model="newUser.email">
                </div>
                <div class="checkbox">
                    <label>
                        <input name="agreed" type="checkbox" 
                               ng-model="newUser.agreed" required>
                        I agree to the terms and conditions
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-block"
                        ng-disabled="myForm.$invalid">
                    OK
                </button>
            </div>
            <div class="well">
                Message: {{message}}
                <div>
                    Valid: {{myForm.$valid}}
                </div>
            </div>
        </form>
    </div>
</body>
</html>
```
![表单验证](2.png)

上述demo在浏览器加载的时候OK按钮是被禁用的，只有文本框内输入合法的值（邮箱会被验证）并勾选复选框后Ok按钮才会变为可用可点击状态，允许用户提交表单。

要向通过ng处理到form元素，并自动设置一些表单元素的校验工作，需要设置一些属性，比如form表单的name属性。

