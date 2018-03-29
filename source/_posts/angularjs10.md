---
title: AngularJS入门笔记-10-使用deployd作为服务器，增加订单功能
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - deployd
date: 2017-05-11 17:45:58
updated: 2017-05-11 17:45:58
---

本次使用deployd作为服务器，同时使用远程mongodb数据库，而不再是connect-static静态服务了，同时添加订单功能。

关于deployd的安装和环境问题，参考上一篇博客。

完成后的界面：

![界面](1.png)

### 目录组织结构：
```s
angularjs #项目目录
  .dpd #deployd的一些信息
    pids
    keys.json #保存dpd keygen命令生成的key
  data
  public #deployd项目生成的静态文件目录
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
      angular-route.js #路由插件
    views #存放视图
      checkoutSummary.html #计算总价视图
      productList.html #展示产品列表
      placeOrder.html #订单运送地址收集
      thankYou.html #完成订单展示页面，通知用户订单号
    angular.js #文件
    bootstrap-theme.css
    bootstrap.css
    app.html #静态应用入口文件
  resources #此目录保存deployd dashboard中创建的表的结构（严格来说不是表而是collection）
    orders #order表
      config.json #order表结构（字段类型）
    products
      config.json
  app.dpd
  server.js #启动deployd服务器脚本
node_modules #node包目录
package.json #项目信息，包括启动服务器脚本
```

先用`npm init`初始化一个空目录，然后在目录内使用`dpd create angularjs`生成一个dpd项目，然后将server.js文件放入该目录下，作为启动dpd服务器的脚本。

```js
// server.js
var deployd = require('deployd');

var server = deployd({
  port: process.env.PORT || 5000,
  env: 'demo',
  db: {
    host: '远程mongodb域名或ip',
    port: 端口,
    name: '数据库名',
    credentials: {
      username: '用户名',
      password: '密码'
    }
  }
});

server.listen();

server.on('listening', function() {
  console.log("Demo Server in 5000");
});

server.on('error', function(err) {
  console.error(err);
  process.nextTick(function() { // Give the server a chance to return an error
    process.exit();
  });
});
```

然后在生成的angularjs目录下找到public,其内会有一个index.html作为默认首页，但本项目不需要使用此页面，直接将以前写好的angularjs项目放入public目录即可。

由于添加了订单功能（即收集用户地址，完善订单功能），所以我们需要一个新的视图，即placeOrder.html文件内容:
```html
<!-- 其实仅是一个form表单采集地址信息 -->
<style>
    /* 为ng默认的验证class添加样式 */
    .ng-invalid {
        background-color: lightpink;
    }

    .ng-valid {
        background-color: lightgreen;
    }

    span.error {
        color: red;
        font-weight: bold;
    }
</style>

<h2>Check out now</h2>
<p>Please enter your details, and we'll ship your goods right away!</p>
<!-- 使用novalidate属性关闭浏览器的自带H5表单验证 -->
<form name="shippingForm" novalidate>
    <div class="well">
        <h3>Ship to</h3>
        <div class="form-group">
            <label>Name</label>
            <input name="name" class="form-control" ng-model="data.shipping.name" required />
            <!-- 通过在name属性上绑定$error以及对应的required属性，然后通过ng-show命令自动的显示/隐藏错误信息 -->
            <span class="error" ng-show="shippingForm.name.$error.required">
                Please enter a name
            </span>

        </div>

        <h3>Address</h3>

        <div class="form-group">
            <label>Street Address</label>
            <input name="street" class="form-control" ng-model="data.shipping.street" required />
            <span class="error" ng-show="shippingForm.street.$error.required">
                Please enter a street address
            </span>
        </div>

        <div class="form-group">
            <label>City</label>
            <input name="city" class="form-control" ng-model="data.shipping.city" required />
            <span class="error" ng-show="shippingForm.city.$error.required">
                Please enter a city
            </span>
        </div>

        <div class="form-group">
            <label>State</label>
            <input name="state" class="form-control" ng-model="data.shipping.state" required />
            <span class="error" ng-show="shippingForm.state.$error.required">
                Please enter a state
            </span>
        </div>

        <div class="form-group">
            <label>Zip</label>
            <input name="zip" class="form-control" ng-model="data.shipping.zip" required />
            <span class="error" ng-show="shippingForm.zip.$error.required">
                Please enter a zip code
            </span>
        </div>

        <div class="form-group">
            <label>Country</label>
            <input name="country" class="form-control" ng-model="data.shipping.country" required />
            <span class="error" ng-show="shippingForm.country.$error.required">
                Please enter a country
            </span>
        </div>

        <h3>Options</h3>
        <div class="checkbox">
            <label>
                <input name="giftwrap" type="checkbox"
                       ng-model="data.shipping.giftwrap" />
                Gift wrap these items
            </label>
        </div>

        <div class="text-center">
            <!-- 绑定完成订单按钮，将所有表单内绑定的数据发送到后台 -->
            <button ng-disabled="shippingForm.$invalid" ng-click="sendOrder(data.shipping)" class="btn btn-primary">
                Complete order
            </button>
        </div>
    </div>
</form>
```

thankYou.html文件内容如下，即通知用户订单号，若下单失败显示错误信息：
```html
<div class="alert alert-danger" ng-show="data.orderError">
    Error ({{data.orderError.status}}). The order could not be placed.
    <a href="#/placeorder" class="alert-link">Click here to try again</a>
</div>

<div class="well" ng-hide="data.orderError">
    <h2>Thanks!</h2>
    Thanks for placing your order. We'll ship your goods as soon as possible.
    If you need to contact us, use reference {{data.orderId}}.
</div>
```

然后将package.json中的scripts中的start项修改如下：
```js
"scripts": {
  "test": "echo \"Error: no test specified\" && exit 1",
  "start": "cd angularjs && node server.js"
}
```

此时就可以在项目中使用`npm start`启动应用了，访问`localhost:5000/app.html`即可看到熟悉的sports store的界面了，但是由于没有配置接口路径无法获取服务器的数据，此时我们访问`localhost:5000/dashboard`，然后填入dpd 生成的key就可以访问dpd为什么生成的数据库操作界面了。

然后依次添加两个表，分别是orders和products表，具体字段和预设内容（手动录入一些测试数据）如下：

![orders表字段](2.png)

![products表测试数据](3.png)

其实dpd在我们生成表的时候，已经将关于表的CRUD的接口为我们写好了，通过dashboard中表中的API菜单即可看到，比如products表的接口如下：

![products表api](4.png)

可以测试`/products`接口，发现dpd服务器自动为我们返回了json格式的products数据。

由于本次server.js中使用的端口默认为5000（也可在执行server.js时指定其他端口），所以需要将sportsStore.js文件中的`dataUrl` 和 `orderUrl`修改为对应的接口地址，同时:
```js
angular.module("sportsStore")
    .constant("dataUrl", "/products") // 修改接口地址，使用dpd提供的接口
    .constant("orderUrl", "/orders")
    .controller("sportsStoreCtrl", function($scope, $http, $location,
        dataUrl, orderUrl, cart) {

        $scope.data = {};

        $http.get(dataUrl)
            .success(function(data) {
                $scope.data.products = data;
            })
            .error(function(error) {
                $scope.data.error = error;
            });
        // 同时我们添加了发送订单功能，即将购物车内的物品
        $scope.sendOrder = function(shippingDetails) {
            var order = angular.copy(shippingDetails);
            order.products = cart.getProducts();
            $http.post(orderUrl, order)
                .success(function(data) { //成功下单后获取服务器响应的数据
                    $scope.data.orderId = data.id; // 将响应中的id设置到全局作用域的订单id
                    cart.getProducts().length = 0; // 然后清空购物车
                })
                .error(function(error) {
                    $scope.data.orderError = error;
                }).finally(function() {
                    $location.path("/complete"); // 最后使用ng的$location而不是js原生的location将路径跳转到完成视图
                });
        }
    });
```
然后刷新app.html页面，就可以看到本博客上最开始的那张界面了。

依次点击商品加入购物车，然后点击Place Order按钮，然后填写地址信息，完成订单即可，最后会显示订单编号。

![填写地址](5.png)

![显示订单号](6.png)

然后可以在dashboard查看订单，显示的订单号即为刚刚完成的订单
![在dashboard查看订单](7.png)


[博客源码地址](https://github.com/xmoyKing/pro-ng-learning-test/tree/master/demo-source-ng10)