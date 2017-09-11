---
title: angularjs入门笔记-16-过滤器
categories:
  - angularjs
tags:
  - angularjs
date: 2017-05-25 19:03:45
updated:
---

过滤器在指令将数据处理并显示到视图之前，对数据进行转换而不必修改作用域中原有的数据，这样能够允许同一份数据在应用中的不同部分以不同形式展示。
过滤器可以执行任何类型的转换，多少情况下用于格式化或对数据进行排序。

一些内置过滤器以及相关知识点：
- currency 格式化为货币
- number 格式化通用数字
- date 格式化为日期
- uppercase/lowercase 大小写
- json 将js对象转化为json格式
- 通过script向html添加本地文件
- limitTo 规定数量的数组中的元素
- orderBy 对数组进行排序
- Module.filter 指定工厂函数，生成一个执行过滤器函数
- 使用$filter 服务，访问和调用其它过滤器

### 为什么不在控制器中过滤数据？
有的时候，将数据转换或格式化的逻辑放在控制器中时很方便而且快捷的事儿，为什么需要将过滤器放在视图中使用呢？

主要是因为，在控制器中转换数据然后直接输出限制了数据的使用方式，因为只有转化后的而没有原始数据，就无法将这个数据用在其他方法或视图中了。

使用过滤器的好处在于，能保留作用域中数据的完整性，将格式化逻辑放在控制器之外意味着能在整个应用中使用，且易于测试和维护。

```html
<html ng-app="exampleApp">
<head>
    <title>Filters</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .controller("defaultCtrl", function ($scope) {
                $scope.products = [
                    { name: "Apples", category: "Fruit", price: 1.20, expiry: 10 },
                    { name: "Bananas", category: "Fruit", price: 2.42, expiry: 7 },
                    { name: "Pears", category: "Fruit", price: 2.02, expiry: 6 },
             
                    // ...other data objects omitted for brevity...
                ];

                $scope.getExpiryDate = function (days) {
                    var now = new Date();
                    return now.setDate(now.getDate() + days);
                }
            });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3>
                Products
                <span class="label label-primary">{{products.length}}</span>
            </h3>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <td>Name</td><td>Category</td>
                        <td>Expiry</td><td class="text-right">Price</td>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="p in products">
                        <td>{{p.name | uppercase }}</td>
                        <td>{{p.category | lowercase }}</td>
                        <td>{{getExpiryDate(p.expiry) | date:"dd MMM yy"}}</td>
                        <td class="text-right">${{p.price | number:0 }}</td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
```

其中，date过滤器支持一些快捷格式字符串：
- medium 相当于MMM d, y h:mm:ss a
- short M/d/yy h:mm a
...

### 过滤集合
ng包含三个内置的集合过滤器，同时也支持自定义过滤器。

limitTo可以限制数组中取出的项目的数量
filter过滤器用于从数据中选择一些对象，条件为指定的表达式，或者一个函数
orderBy可对数组中的对象进行排序，指定一个表达式或函数，最简单的是指定为属性名，若在属性名前加负号，表示排序方向

```html
<html ng-app="exampleApp">
<head>
    <title>Filters</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
            .controller("defaultCtrl", function ($scope) {
                $scope.products = [
                    { name: "Apples", category: "Fruit", price: 1.20, expiry: 10 },
                    { name: "Bananas", category: "Fruit", price: 2.42, expiry: 7 },
                    { name: "Pears", category: "Fruit", price: 2.02, expiry: 6 },

                    { name: "Tuna", category: "Fish", price: 20.45, expiry: 3 },
                    { name: "Salmon", category: "Fish", price: 17.93, expiry: 2 },
                    { name: "Trout", category: "Fish", price: 12.93, expiry: 4 },

                    { name: "Beer", category: "Drinks", price: 2.99, expiry: 365 },
                    { name: "Wine", category: "Drinks", price: 8.99, expiry: 365 },
                    { name: "Whiskey", category: "Drinks", price: 45.99, expiry: 365 }
                ];

                $scope.limitVal = "5";
                $scope.limitRange = [];
                for (var i = (0 - $scope.products.length); 
                        i <= $scope.products.length; i++) {
                    $scope.limitRange.push(i.toString());
                }

                
                $scope.selectItems = function (item) {
                    return item.category == "Fish" || item.name == "Beer";
                };

                
                $scope.myCustomSorter = function (item) {
                    return item.expiry < 5 ? 0 : item.price;
                }      
            });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3>
                Products
                <span class="label label-primary">{{products.length}}</span>
            </h3>
        </div>
        <div class="panel-body">
            Limit: <select ng-model="limitVal" 
                ng-options="item for item in limitRange"></select>
        </div>
        <div class="panel-body">
<!--limitTo使用  -->
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <td>Name</td>
                        <td>Category</td>
                        <td>Expiry</td>
                        <td class="text-right">Price</td>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="p in products | limitTo:limitVal">
                        <td>{{p.name}}</td>
                        <td>{{p.category}}</td>
                        <td>{{p.expiry}}</td>
                        <td class="text-right">{{p.price | currency }}</td>
                    </tr>
                </tbody>          
            </table>
<!--filter使用  -->
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <td>Name</td>
                        <td>Category</td>
                        <td>Expiry</td>
                        <td class="text-right">Price</td>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="p in products | filter:selectItems">
                        <td>{{p.name}}</td>
                        <td>{{p.category}}</td>
                        <td>{{p.expiry}}</td>
                        <td class="text-right">{{p.price | currency }}</td>
                    </tr>
                </tbody>
            </table>
<!--orderBy使用  -->
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <td>Name</td>
                        <td>Category</td>
                        <td>Expiry</td>
                        <td class="text-right">Price</td>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="p in products | orderBy:'-price'">

                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
```

可以对orderBy使用一个数组，数组元素为属性名或函数名，用于依次进行排序。
```js
orderBy: [myCustomSorter, '-price']
```

过滤器也可以串联起来,一般是用于集合，对单个数据使用串联过滤器没什么必要：
```js
ng-repeat="p in products | orderBy:[myCustomSorter, '-price'] | limitTo: 5"
```

### 自定义过滤器
通过Module.filter能够自定义过滤器，创建一个按照自定义规则格式化数据的过滤器。

比如:
labelCase格式化一个字符串为首字母大写其他小写/首字母小写其他大写,
skip跳过一定数量的项，然后正常返回后面的值
take将limitTo和skip结合起来
```js
angular.module("exampleApp")
    .filter("labelCase", function () {
        return function (value, reverse) {
            if (angular.isString(value)) {
                var intermediate =  reverse ? value.toUpperCase() : value.toLowerCase();
                return (reverse ? intermediate[0].toLowerCase() :
                    intermediate[0].toUpperCase()) + intermediate.substr(1);
            } else {
                return value;
            }
        }
    })
    .filter("skip", function () {
        return function (data, count) {
            if (angular.isArray(data) && angular.isNumber(count)) {
                if (count > data.length || count < 1) {
                    return data;
                } else {
                    return data.slice(count);
                }
            } else { 
                return data;
            }
        }
    })
    .filter("take", function ($filter) {
        return function (data, skipCount, takeCount) {
            var skippedData = $filter("skip")(data, skipCount);
            return $filter("limitTo")(skippedData, takeCount);
        }
    });
```

使用：
```html

<tr ng-repeat="p in products | orderBy:[myCustomSorter, '-price'] | limitTo: 5">
    <td>{{p.name | labelCase }}</td>
    <td>{{p.category | labelCase:true }}</td>
    <td>{{p.expiry}}</td>
    <td class="text-right">{{p.price | currency }}</td>
</tr>

ng-repeat="p in products | skip:2 | limitTo: 5"

ng-repeat="p in products | take:2:5"
```