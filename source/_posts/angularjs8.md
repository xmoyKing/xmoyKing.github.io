---
title: angularjs入门笔记-8-购物网站产品列表Demo
categories:
  - fe
tags:
  - fe
  - angularjs
date: 2017-05-05 10:56:41
updated: 2017-05-05 10:56:41
---

延续上次学习的静态demo，angularjs6，本次做一个简单的实现分页，可分类展示产品的购物小demo

产品列表界面效果如下图:
![产品列表Demo界面](1.png)

### 目录组织结构：
```s
angularjs #项目目录
  controllers #控制器文件夹
    sportsStore.js #全局模块-公用控制器
    productListController.js #仅仅只用于展示产品的控制器
  filters #过滤器文件夹
    customFilters.js #在该文件中定义所有的过滤器
  angular.js #文件
  bootstrap-theme.css
  bootstrap.css
  app.html #静态html文件
node_modules #node包目录
server.js #启动静态服务器的入口文件
```

### 静态html文件app.html
```html
<!DOCTYPE html>
<html ng-app="sportsStore"> <!-- 绑定模块 -->
<head>
    <title>SportsStore</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("sportsStore", ["customFilters"]); // 初始化模块，并指定依赖，此处的依赖为声明，并不需要该依赖已经存在
    </script>
    <script src="controllers/sportsStore.js"></script>
    <script src="filters/customFilters.js"></script>
    <script src="controllers/productListControllers.js"></script>
</head>
<body ng-controller="sportsStoreCtrl"> <!-- 全局控制器 -->
    <div class="navbar navbar-inverse">
        <a class="navbar-brand" href="#">SPORTS STORE</a>
    </div>
    <div class="panel panel-default row" ng-controller="productListCtrl"> <!-- 产品列表控制器，继承全局控制器的作用域 -->
        <!-- 分类列表 -->
        <div class="col-xs-3">
            <a ng-click="selectCategory()"
               class="btn btn-block btn-default btn-lg">Home</a>  <!-- 绑定函数，切换分类 -->
            <!-- 迭代每一个唯一的分类，同时在其上绑定点击事件 -->
            <a ng-repeat="item in data.products | orderBy:'category' | unique:'category'"
               ng-click="selectCategory(item)" class=" btn btn-block btn-default btn-lg"
               ng-class="getCategoryClass(item)">
                {{item}}
            </a>
        </div>
        <!-- 产品列表 -->
        <div class="col-xs-8">
            <!-- 使用过滤器，将所有产品中的对应分类以及当前的页数的产品过滤出来，然后用repeat迭代 -->
            <div class="well"
                 ng-repeat="item in data.products | filter:categoryFilterFn | range:selectedPage:pageSize">
                <h3>
                    <strong>{{item.name}}</strong>
                    <span class="pull-right label label-primary">
                        {{item.price | currency}}
                    </span>
                </h3>
                <span class="lead">{{item.description}}</span>
            </div>
            <!-- 分页页码 -->
            <div class="pull-right btn-group">
                <a ng-repeat="page in data.products | filter:categoryFilterFn | pageCount:pageSize"
                   ng-click="selectPage($index + 1)" class="btn btn-default"
                   ng-class="getPageClass($index + 1)">
                    {{$index + 1}}
                </a>
            </div>
        </div>
</body>
</html>
```

### 全局模块-公用控制器 sportsStore.js
```js
/// <reference path="../angular.js" />
angular.module("sportsStore") // 此处仅是获取sportsStore模块（已在其他文件中初始化了）
.controller("sportsStoreCtrl", function ($scope) { // 创建名为sportsStoreCtrl控制器
    // 在scope上定义一个data对象作为模型，products数组表示所有的产品
    // 每一个产品有独立的名称、价格以及描述，所属的分类
    $scope.data = {
        products: [ // 此处的products先模拟4个产品用于自测（以后可使用ajax从服务器获取产品列表）
            {
                name: "Product #1", description: "A product",
                category: "Category #1", price: 100
            },
            {
                name: "Product #2", description: "A product",
                category: "Category #1", price: 110
            },
            {
                name: "Product #3", description: "A product",
                category: "Category #2", price: 210
            },
            {
                name: "Product #4", description: "A product",
                category: "Category #3", price: 202
            }]
    };
});
```

### 仅仅只用于展示产品的控制器productListController.js 
之所以不在sportsStoreCtrl控制器中是由于考虑到还有其他模块及其对应的控制器，所以将产品列表的控制器独立出来
```js
/// <reference path="../angular.js" />

angular.module("sportsStore") // 此处仅是获取sportsStore模块（已在其他文件中初始化了）
    .constant("productListActiveClass", "btn-primary") // 此处定义常量productListActiveClass，表示选中项的class名称
    .constant("productListPageCount", 3) // 此处定义常量productListPageCount，表示分页时每页产品的数量
    .controller("productListCtrl", function ($scope, $filter, // 定义产品列表控制器，在函数参数中先指定依赖的模块或服务或常量
        productListActiveClass, productListPageCount) {

        var selectedCategory = null; // 表示当前所选择的产品类别

        $scope.selectedPage = 1; // 表示当前所在页数
        $scope.pageSize = productListPageCount; // 表示每页产品数量

        // 视图上绑定分类列表项函数
        $scope.selectCategory = function (newCategory) {
            selectedCategory = newCategory;
            $scope.selectedPage = 1;
        }
        // 选定页数的函数
        $scope.selectPage = function (newPage) {
            $scope.selectedPage = newPage;
        }
        // 过滤分类
        $scope.categoryFilterFn = function (product) {
            return selectedCategory == null ||
                product.category == selectedCategory;
        }
        // 确定分类的class
        $scope.getCategoryClass = function (category) {
            return selectedCategory == category ? productListActiveClass : "";
        }
        // 确定当前页的class
        $scope.getPageClass = function (page) {
            return $scope.selectedPage == page ? productListActiveClass : "";
        }
    });
```

#在该文件中定义所有的过滤器customFilters.js 
```js
/// <reference path="../angular.js" />

angular.module("customFilters", []) // 定义customFilters模块，作为过滤器集合
.filter("unique", function () { // 返回data中所有唯一的propertyName属性数组
    return function (data, propertyName) {
        if (angular.isArray(data) && angular.isString(propertyName)) {
            var results = [];
            var keys = {};
            for (var i = 0; i < data.length; i++) {
                var val = data[i][propertyName];
                if (angular.isUndefined(keys[val])) {
                    keys[val] = true;
                    results.push(val);
                }
            }
            return results;
        } else {
            return data;
        }
    }
})
.filter("range", function ($filter) { // 获取指定区间的索引数目，以数组形式返回
    return function (data, page, size) {
        if (angular.isArray(data) && angular.isNumber(page) && angular.isNumber(size)) {
            var start_index = (page - 1) * size; // 获取本页面开始的产品索引数
            if (data.length < start_index) {
                return [];
            } else {
                // $filter("limitTo")(input, size)方法，选取input数组中的前size个记录，
                return $filter("limitTo")(data.splice(start_index), size);                 
            }
        } else {
            return data;
        }
    }
})
.filter("pageCount", function () { // 根据data和size，计算页面总数
    return function (data, size) {
        if (angular.isArray(data)) {
            var result = [];
            for (var i = 0; i < Math.ceil(data.length / size) ; i++) {
                result.push(i);
            }
            return result;
        } else {
            return data;
        }
    }
});
```


[博客源码地址](https://github.com/xmoyKing/pro-ng-learning-test/tree/master/demo-source-ng8)