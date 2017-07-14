---
title: angularjs入门笔记-9-购物网站-添加购物车和路由功能
categories:
  - fe
tags:
  - fe
  - angularjs
date: 2017-05-08 14:09:25
updated: 2017-05-08 14:09:25
---

一个网站不可能只在一个html文件就完成了的，一般都有多个html文件以及多个不同的组件或者模块，这就需要后端定义路由（即url地址）与组件代码片段，而angularjs有一个路由的插件可以将路由功能放在前端完成，同时提供了组件功能，在上一个篇的基础上，本次加上路由功能和购物车。

[接上一篇：angularjs入门笔记-8-静态购物网站产品列表Demo](https://xmoyking.github.io/2017/05/05/angularjs8/)


有添加到购物车功能后的产品列表界面效果如下图:
![产品列表](1.png)

购物车界面如下图：
![购物车界面](2.png)


### 目录组织结构：
```s
angularjs #项目目录
  components #组件文件夹
    cart #购物车组件
      cart.js #购物车模块
      cartSummary.html #购物车视图代码
  controllers #控制器文件夹+
    sportsStore.js #全局模块-公用控制器
    productListController.js #仅仅只用于展示产品的控制器
    checkoutControllers.js #检查购物车模块
  filters #过滤器文件夹
    customFilters.js #在该文件中定义所有的过滤器
  ngmodules #存放ng的插件
    angular-route.js
  views #存放视图
    checkoutSummary.html
    productList.html
  angular.js #文件
  bootstrap-theme.css
  bootstrap.css
  app.html #静态应用入口文件
  products.json #模拟从服务获取的json数据
node_modules #node包目录
server.js #启动静态服务器的入口文件
```

### 应用入口app.html
随着应用的功能增加，逻辑变的复杂许多，html元素更是错综复杂，没办法一下弄清楚每个元素完成的功能，此时需要将这些标签拆分，使用ng-include指令在运行时引入这些局部视图。

使用局部视图有如下好处：
1. 将应用拆分为可独立管理的块
2. 在应用范围内的可复用性
3. 易于与不同的路由url结合，修改对应的视图

```html
<!DOCTYPE html>
<html ng-app="sportsStore">
<head>
    <title>SportsStore</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("sportsStore", ["customFilters", "cart", "ngRoute"])
        .config(function ($routeProvider) {
            // 定义每个路由路径对应的局部视图
            $routeProvider.when("/complete", {
                templateUrl: "/views/thankYou.html"
            });

            $routeProvider.when("/placeorder", {
                templateUrl: "/views/placeOrder.html"
            });

            $routeProvider.when("/checkout", {
                templateUrl: "/views/checkoutSummary.html"
            });

            $routeProvider.when("/products", {
                templateUrl: "/views/productList.html"
            });

            $routeProvider.otherwise({
                templateUrl: "/views/productList.html"
            });
        });
    </script>
    <script src="controllers/sportsStore.js"></script>
    <script src="filters/customFilters.js"></script>
    <script src="controllers/productListControllers.js"></script>
    <script src="components/cart/cart.js"></script>
    <script src="ngmodules/angular-route.js"></script>
    <script src="controllers/checkoutControllers.js"></script>
</head>
<body ng-controller="sportsStoreCtrl">
    <div class="navbar navbar-inverse">
        <a class="navbar-brand" href="#">SPORTS STORE</a>
        <cart-summary /> <!-- 自定义指令cart-summary -->
    </div>
    <!-- 错误提示框，当出现错误时显示错误信息  -->
    <div class="alert alert-danger" ng-show="data.error">
        Error ({{data.error.status}}). The product data was not loaded.
        <a href="/app.html" class="alert-link">Click here to try again</a>
    </div>
    <ng-view />   <!-- 加载局部视图的指令  -->
</body>
</html>
```



### 全局模块-公用控制器 sportsStore.js
将模型修改为通过后端服务器获取
```js
angular.module("sportsStore")
    .constant("dataUrl", "products.json") // 定义常量，此处的products.json可以改为某个url接口
    .controller("sportsStoreCtrl", function ($scope, $http, dataUrl) {

        $scope.data = {};
        // 使用http服务将模型加载到应用内
        $http.get(dataUrl)
            .success(function (data) {
                $scope.data.products = data;
            })
            .error(function (error) {
                $scope.data.error = error;
            });
    });
```

### products.json
此json数据多了一个id字段标识每一种产品，当加入购物车时以id字段作为标识
```js
[{  
    "id": "ID1",
    "name": "Product #1",
    "description": "A product",
    "category": "Category #1", 
    "price": 100
},
{
    "id": "ID2",
    "name": "Product #2", 
    "description": "A product",
    "category": "Category #1", 
    "price": 110
},
{
    "id": "ID3",
    "name": "Product #3", 
    "description": "A product",
    "category": "Category #2", 
    "price": 210
},
{
    "id": "ID4",
    "name": "Product #4", 
    "description": "A product",
    "category": "Category #3", 
    "price": 202
}]
```


### 产品列表视图 productList.html
将产品列表部分从app.html中抽离出，
```html
<div class="panel panel-default row" ng-controller="productListCtrl"
     ng-hide="data.error">
    <div class="col-xs-3">
        <a ng-click="selectCategory()"
           class="btn btn-block btn-default btn-lg">Home</a>
        <a ng-repeat="item in data.products | orderBy:'category' | unique:'category'"
           ng-click="selectCategory(item)" class=" btn btn-block btn-default btn-lg"
           ng-class="getCategoryClass(item)">
            {{item}}
        </a>
    </div>
    <div class="col-xs-8">
        <div class="well"
             ng-repeat="item in data.products | filter:categoryFilterFn | range:selectedPage:pageSize">
            <h3>
                <strong>{{item.name}}</strong>
                <span class="pull-right label label-primary">
                    {{item.price | currency}}
                </span>
            </h3>
            <!-- 添加产品到购物车按钮  -->
            <button ng-click="addProductToCart(item)"
                    class="btn btn-success pull-right">
                Add to cart
            </button>
            <span class="lead">{{item.description}}</span>
        </div>
        <div class="pull-right btn-group">
            <a ng-repeat="page in data.products | filter:categoryFilterFn | pageCount:pageSize"
               ng-click="selectPage($index + 1)" class="btn btn-default"
               ng-class="getPageClass($index + 1)">
                {{$index + 1}}
            </a>
        </div>
    </div>
</div>
```

### 购物车模块/服务 cart.js
比如购物车这样独立的功能可以将其放在组件文件夹内，而不是将其控制器和视图放在公共区域，
在新模块cart中自定义服务，使用factory方法，传入服务的名称和函数，工厂模式将在该服务需要时被调用（而不是应用一开始就被调用），通过这个工厂函数只调用一次。
```js
angular.module("cart", [])
.factory("cart", function () {

    var cartData = [];

    return {
        // 添加指定的产品到购物车，若购物车已包含该产品，则增加数量
        addProduct: function (id, name, price) {
            var addedToExistingItem = false;
            for (var i = 0; i < cartData.length; i++) {
                if (cartData[i].id == id) {
                    cartData[i].count++;
                    addedToExistingItem = true;
                    break;
                }
            }
            if (!addedToExistingItem) {
                cartData.push({
                    count: 1, id: id, price: price, name: name
                });
            }
        },
        // 删除指定的产品
        removeProduct: function (id) {
            for (var i = 0; i < cartData.length; i++) {
                if (cartData[i].id == id) {
                    cartData.splice(i, 1);
                    break;
                }
            }
        },
        // 返回购物车中所有产品
        getProducts: function () {
            return cartData;
        }
    }
})
// 创建一个购物车结算指令，该指令能在多个页面中复用
.directive("cartSummary", function (cart) { // 自定义指令需要传入两个参数，一个为指令名称，一个为指令执行时调用的工厂函数
    return { // 返回一个对象，该对象定义了指令的各种属性
        restrict: "E", // 以元素标签的形式调用该指令（可简单的当做是自定义的html标签即可）
        templateUrl: "components/cart/cartSummary.html", // 指定指令的局部视图，即完成后的html元素内容
        controller: function ($scope) { // 指定该指令的局部控制器

            var cartData = cart.getProducts();
            // 计算总价
            $scope.total = function () {
                var total = 0;
                for (var i = 0; i < cartData.length; i++) {
                    total += (cartData[i].price * cartData[i].count);
                }
                return total;
            }
            // 计算产品的总量
            $scope.itemCount = function () {
                var total = 0;
                for (var i = 0; i < cartData.length; i++) {
                    total += cartData[i].count;
                }
                return total;
            }
        }
    };
});
```

### 购物车统计总价和总量视图 cartSummary.html
```html
<style>
    .navbar-right { float: right !important; margin-right: 5px; }
    .navbar-text { margin-right: 10px; }
</style>

<div class="navbar-right">
    <div class="navbar-text">
        <b>Your cart:</b>
        {{itemCount()}} item(s),
        {{total() | currency}}
    </div>
    <a href="#/checkout" class="btn btn-default navbar-btn">Checkout</a>
</div>
```

### 购物车控制器 checkoutControllers.js
```js
angular.module("sportsStore")
.controller("cartSummaryController", function ($scope, cart) {
    // 从自定义cart服务中获取已加入购物车的产品
    $scope.cartData = cart.getProducts();
    // 计算总价
    $scope.total = function () {
        var total = 0;
        for (var i = 0; i < $scope.cartData.length; i++) {
            total += ($scope.cartData[i].price * $scope.cartData[i].count);
        }
        return total;
    }
    // 从购物车中移除指定id的产品
    $scope.remove = function (id) {
        cart.removeProduct(id);
    }
});
```

### 购物车视图 checkoutSummary.html
```html
<h2>Your cart</h2>

<div ng-controller="cartSummaryController">

    <div class="alert alert-warning" ng-show="cartData.length == 0">
        There are no products in your shopping cart.
        <a href="#/products" class="alert-link">Click here to return to the catalogue</a>
    </div>

    <div ng-hide="cartData.length == 0">
        <table class="table">
            <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Item</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="item in cartData">
                    <td class="text-center">{{item.count}}</td>
                    <td class="text-left">{{item.name}}</td>
                    <td class="text-right">{{item.price | currency}}</td>
                    <td class="text-right">{{ (item.price * item.count) | currency}}</td>
                    <td>
                        <button ng-click="remove(item.id)"
                                class="btn btn-sm btn-warning">
                            Remove
                        </button>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="text-right">
                        {{total() | currency}}
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="text-center">
            <a class="btn btn-primary" href="#/products">Continue shopping</a>
            <a class="btn btn-primary" href="#/placeorder">Place order now</a>
        </div>
    </div>
</div>
```


[博客源码地址](https://github.com/xmoyKing/pro-ng-learning-test/tree/master/demo-source-ng9)