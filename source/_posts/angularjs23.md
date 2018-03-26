---
title: AngularJS入门笔记-23-视图服务
categories:
  - AngularJS
tags:
  - AngularJS
date: 2017-07-05 10:28:37
updated:
---

ng的视图服务通过多个组件独立控制内容，能降低应用复杂度，比如：
- 使用$routeProvider定义url路由，使路由导航由前端控制
- 使用ng-view指令显示视图
- 使用$location.path方法或使用href属性改变路由
- 配置controller属性将视图和控制器关联
- 配置resolve属性定义控制器的依赖

使用事件绑定的方式，若当前路由变化时调用，参数的值以名称索引集合的形式表现的，路由参数的值通过$routeParams服务获取。
- $routeChangeStart 路由改变前触发
- $routeChangeSuccess 路由改变后触发
- $routeChangeError 路由不能改变时触发
- $routeUpdate 路由刷新时触发，其实时绑定了reloadOnSearch属性上

```js
$scope.$on("$routeChangeSuccess", function () {
    if ($location.path().indexOf("/edit/") == 0) {
        var id = $routeParams["id"];
        for (var i = 0; i < $scope.products.length; i++) {
            if ($scope.products[i].id == id) {
                $scope.currentProduct = $scope.products[i];
                break;
            }
        }
    }
});
```


路由配置项：
- controller 指定与路由显示的视图关联的控制器名称
- controllerAs 指定控制器的别名
- template 指定视图的内容，可以使html字符串或返回html字符串的函数
- templateUrl 指定路由所匹配实现的视图文件的URL, 可以使字符串或返回字符串的函数
- resolve 指定一组控制器的依赖
- redirectTo 指定当路由匹配时浏览器应重定向的目标路径
- reloadOnSearch 默认为true，仅当$location的search和hash方法改变返回值时，路由重载
- caseInsensitiveMatch 默认为true，路由匹配大小写不敏感，即/Edit和/edit相同

```js
angular.module("exampleApp", ["increment", "ngResource", "ngRoute"])
.constant("baseUrl", "http://localhost:5500/products/")
.factory("productsResource", function ($resource, baseUrl) {
    return $resource(baseUrl + ":id", { id: "@id" },
            { create: { method: "POST" }, save: { method: "PUT" } });
})
.config(function ($routeProvider, $locationProvider) {

    $locationProvider.html5Mode(true);

    $routeProvider.when("/edit/:id", {
        templateUrl: "/editorView.html",
        controller: "editCtrl"
    });

    $routeProvider.when("/create", {
        templateUrl: "/editorView.html",
        controller: "editCtrl"
    });

    $routeProvider.otherwise({
        templateUrl: "/tableView.html",
        controller: "tableCtrl",
        resolve: {
            data: function (productsResource) {
                return productsResource.query();
            }
        }
    });
})
.controller("defaultCtrl", function ($scope, $location, productsResource) {

    $scope.data = {};

    $scope.createProduct = function (product) {
        new productsResource(product).$create().then(function (newProduct) {
            $scope.data.products.push(newProduct);
            $location.path("/list");
        });
    }

    $scope.deleteProduct = function (product) {
        product.$delete().then(function () {
            $scope.data.products.splice($scope.data.products.indexOf(product), 1);
        });

        $location.path("/list");
    }
})
.controller("tableCtrl", function ($scope, $location, $route, data) {
    $scope.data.products = data;

    $scope.refreshProducts = function () {
        $route.reload();
    }
})
.controller("editCtrl", function ($scope, $routeParams, $location) {

    $scope.currentProduct = null;

    if ($location.path().indexOf("/edit/") == 0) {
        var id = $routeParams["id"];
        for (var i = 0; i < $scope.data.products.length; i++) {
            if ($scope.data.products[i].id == id) {
                $scope.currentProduct = $scope.data.products[i];
                break;
            }
        }
    }

    $scope.cancelEdit = function () {
        $location.path("/list");
    }

    $scope.updateProduct = function (product) {
        product.$save();
        $location.path("/list");
    }

    $scope.saveEdit = function (product) {
        if (angular.isDefined(product.id)) {
            $scope.updateProduct(product);
        } else {
            $scope.createProduct(product);
        }
        $scope.currentProduct = {};
    }
});
```

新的editCtrl控制器的实现将在每次editorView.html视图显示时创建，这意味着不需要使用$route服务事件掌控视图何时变化，只关注控制器函数是否被执行。
以这种方式使用控制器的好处之一是的应用了标准的继承规则，如将editCtrl嵌套如defaultCtrl并能访问它的作用域中的数据和行为，即能在顶级控制器中定义普通数组和功能并在嵌套的控制器中定义特定视图。

resolve属性能指定将被注入controller属性指定的控制器的依赖，所依赖的可以是服务，resolve属性更多用于初始化视图所必须执行的工作，如将promises作为依赖返回，但路由不实现控制器，直到被resolved时才实现。

```js
controller: "tableCtrl",
resolve: {
    data: function (productsResource) {
        return productsResource.query();
    }
}
```
使用resolve属性创建了依赖data，data属性为在tableCtrl控制器被创建前执行的函数，结果会作为参数data传入。