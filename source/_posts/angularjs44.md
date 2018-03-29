---
title: AngularJS巩固实践-44-ng实现前端权限控制
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
date: 2017-08-31 22:43:49
updated:
---

在实践中，项目常常需要对权限进行控制。

在前后端统一的传统架构下的解决方案为：后端判断权限，然后跳转到“登录/拒绝访问”等页面。

而在前后端分离的架构下，这种方案就有问题了，因为页面完全是静态文件，它被缓存在用户的浏览器中，后端只提供API，然后前端把返回结果渲染出来，关键是前端的路由转换不会再通知后端，后端跳转也就没办法了。所以需要新的方案来实现认证和鉴权。

虽然后端的控制力减弱，但前端的控制力却大大加强了，但这本身就是前后端分离的理所应当的结果，后端本就应该提供纯净的业务API，而不应该关系交互逻辑，某种意义上说以前的方案是不符合前后业务分离的。

现在，将交互逻辑返还给前端，后端只要给出正确的返回码即可，比如：需要登录时返回401，权限不足时返回403。

这种应用场景下，ng的解决方案是$http的interceptor，因为每一个后端API都会直接或间接通过$http来调用，所以只要有一个Interceptor来拦截responseError，就能直到服务器端发回的每一条错误消息，只要对这些错误消息的状态码进行判断，就可以进行统一处理。比如：404时弹出对话框，告知用户api不存在，500时就显示错误详情，401时弹出登录框，用户不需跳转其他页面即可完成登录，这个过程不需要任何路由切换，也不需要保存任何状态，最妙的是，可以通过Promise机制来让登录过程对调用者透明。

但有的时候不希望用户进入路由，这种情况该如何解决？ 以ui-router为例

#### 事件方案
ui-router通过$stateChangeStart事件开放对路由切换的控制权,因为需要一个相对集中的权限控制点，所以写再run回调中：
```js
angular.module('com.ngnice.app').run(function($rootScope){
  $rootScope.$on('$stateChangeStart', function(event, state, params){
    var allowed = function(state, params){
       // todo: 根据state和params判断是否可授权，返回true/false
    };

    if(!allowed(state, params)){
      event.preventDefault();
    }
  });
});
```
只要实现allowed即可决定路由是否允许进入

#### resolve方案
路由库中，resolve不是为了支持权限控制页面而设计的，它有非常多的用途。

Controller除了可以注入服务外，其实它也可以注入其他变量，比如resolve提供的变量,假设定义如下路由：
```js
$stateProvider.state('default', {
  url: '',
  templateUrl: 'controller/home/index.html',
  controller: 'HomeIndexController as vm',
  resolve: {
    a: function(){
      return 1;
    },
    b: function(){
      return 'b';
    }
  }
});
```

那么再HomeIndexController中可以注入两个额外变量a和b，分别为1和“b”:
```js
angular.module('com.ngnice.app').controller('HomeIndexController', function(a, b){
  // a为1，b为“b”
});
```
那么如何实现权限控制呢？由于函数可以返回promise，其有两个回调函数，一个成功，一个失败。在路由库拿到这个promise后，就注册两个回调函数，当成功时更改url并渲染页面，失败时不做任何操作，仍然在当前路由，所以可以将代码改为：
```js
$stateProvider.state('default', {
  url: '',
  templateUrl: 'controller/home/index.html',
  controller: 'HomeIndexController as vm',
  resolve: {
    // 卫兵函数，根据条件判断是否可以进入本路由
    guarder: function($q, $http){
      // 权限判断逻辑可修改
      var allowed = false;
      var deferred = $q.defer();
      if(allowed){
        // 正常跳转，甚至时发卡一个网络请求后再异步调用
        deferred.resolve();
      }else{
        // 失败停留
        deferred.reject();
      }

      return deferred.promise;
    }
  }
});
```
注：卫兵函数不能写成服务，否则只会被调用一次，并且存成单例对象，而期望每次进入本路由都执行。

```js
angular.module('com.ngnice.app').config(function($stateProvider, $urlRouterProvider, resolver){
  // ...
  $stateProvider.state('default', {
    url: '',
    resolve: {
      // 需要一个me对象，用来获取当前登录的用户
      me: resolver.me
    },
    templateUrl: 'controller/home/index.html',
    controller: 'HomeIndexController as vm',
  });
  // ...
});


(function(){
  var _me = angular.noop;
  angular.module('com.ngnic.app').constant('resolver', {
    me: function(){
      return _me();
    }
  });

  angular.module('com.ngnice.app').run(function($http){
    _me =function(){
      return $http.get('/api/me');
    }
  });
})();
```