---
title: angularjs入门笔记-22-RESTful
categories:
  - angularjs
tags:
  - angularjs
  - rest
  - deployd
date: 2017-07-02 22:58:23
updated:
---

REST是一种基于HTTP请求的操作的API风格，而不是规范，是否使用REST取决于需求。
- 当使用$http服务，通过显式的用ajax请求使用RESTful API
- 当使用$resource服务，通过不显示的用ajax请求使用RESTful API
- 当自定义动作或重定义默认时，使用$resource服务前剪裁ajax请求

使用RESTful风格的API需要后端支持，本次使用Deployd作为简易后端。关于Deployd，查看如何安装以及简单使用请至官网：[deployd](https://github.com/deployd/deployd#install-from-npm)

### 使用$http服务
RESTful服务用于标准异步HTTP请求，而$http服务提供了获取数据至应用作用域的特性。
```js
angular.module("exampleApp", [])
.constant("baseUrl", "http://localhost:5500/products/") // 设置基本请求接口
.controller("defaultCtrl", function ($scope, $http, baseUrl) {

    $scope.displayMode = "list";
    $scope.currentProduct = null;

    $scope.listProducts = function () { // 获取
        $http.get(baseUrl).success(function (data) {
            $scope.products = data;
        });
    }

    $scope.deleteProduct = function (product) { // 根据id删除
        $http({
            method: "DELETE",
            url: baseUrl + product.id
        }).success(function () {
            $scope.products.splice($scope.products.indexOf(product), 1);
        });
    }

    $scope.createProduct = function (product) { // 创建
      $http.post(baseUrl, product).success(function(newProduct){
          $scope.products.push(newProduct);
          $scope.displayMode = "list";
      });
    }
    
    $scope.updateProduct = function (product) { // 修改
        $http({
            url: baseUrl + product.id,
            method: "PUT",
            data: product
        }).success(function (modifiedProduct) {
            for (var i = 0; i < $scope.products.length; i++) {
                if ($scope.products[i].id == modifiedProduct.id) {
                    $scope.products[i] = modifiedProduct;
                    break;
                }
            }
            $scope.displayMode = "list";
        });
    }
```

### 使用$resouce服务
ng通过$resource服务把ajax请求和url格式的细节隐藏，使得更容易与RESTful数据交互。
$resouce服务不是内置的，而是可选的模块，用时需要下载ngResouce模块。
```js
angular.module("exampleApp", ["increment", "ngResource"])
.constant("baseUrl", "http://localhost:5500/products/")
.controller("defaultCtrl", function ($scope, $http, $resource, baseUrl) {

    $scope.displayMode = "list";
    $scope.currentProduct = null;
    // 配置$resouce服务，第一个参数指定url的格式，其中冒号后面的表示变量，第二个参数指定第一个参数中的变量部分，通过@前缀绑定数据对象的某个属性
    $scope.productsResource = $resource(baseUrl + ":id", { id: "@id" });

    $scope.listProducts = function () { // 获取数据
        $scope.products = $scope.productsResource.query();
    }

    $scope.deleteProduct = function (product) {
        product.$delete().then(function () {
            $scope.products.splice($scope.products.indexOf(product), 1);
        });
        $scope.displayMode = "list";
    }

    $scope.createProduct = function (product) {
        new $scope.productsResource(product).$save().then(function(newProduct) {
            $scope.products.push(newProduct);
            $scope.displayMode = "list";
        });
    }

    $scope.updateProduct = function (product) {
        product.$save(); // 修改
        $scope.displayMode = "list";
    }

    $scope.editOrCreateProduct = function (product) {
        $scope.currentProduct = product ? product : {};
        $scope.displayMode = "edit";
    }

    $scope.saveEdit = function (product) {
        if (angular.isDefined(product.id)) {
            $scope.updateProduct(product);
        } else {
            $scope.createProduct(product);
        }
    }

    $scope.cancelEdit = function () {
        if ($scope.currentProduct && $scope.currentProduct.$get) {
            $scope.currentProduct.$get();
        }
        $scope.currentProduct = {};
        $scope.displayMode = "list";
    }

    $scope.listProducts();
});
```


