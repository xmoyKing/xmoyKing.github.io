---
title: angularjs巩固实践-44-ng实现前端权限控制
categories:
  - angularjs
tags:
  - angularjs
date: 2017-08-31 22:43:49
updated:
---

在实践中，项目常常需要对权限进行控制。

在前后端统一的传统架构下的解决方案为：后端判断权限，然后跳转到“登录/拒绝访问”等页面。

而在前后端分离的架构下，这种方案就有问题了，因为页面完全是静态文件，它被缓存在用户的浏览器中，后端只提供API，然后前端把返回结果渲染出来，关键是前端的路由转换不会再通知后端，后端跳转也就没办法了。所以需要新的方案来实现认证和鉴权。

虽然后端的控制力减弱，但前端的控制力却大大加强了，但这本身就是前后端分离的理所应当的结果，后端本就应该提供纯净的业务API，而不应该关系交互逻辑，某种意义上说以前的方案是不符合前后业务分离的。

现在，将交互逻辑返还给前端，后端只要给出正确的返回码即可，比如：需要登录时返回401，权限不足时返回403。

这种应用场景下，ng的解决方案是$http的interceptor，因为每一个后端API都会直接或间接通过$http来调用，所以只要有一个Interceptor来拦截responseError，就能直到服务器端发回的每一条错误消息，只要对这些错误消息的状态码进行判断，就可以进行统一处理。比如：404时弹出对话框，告知用户api不存在，500时就显示错误详情，401时弹出登录框，用户不需跳转其他页面即可完成登录，这个过程不需要任何路由切换，也不需要保存任何状态，最妙的是，可以通过Promise机制来让登录过程对调用者透明。

但有的时候不希望用户进入路由，这种情况该如何解决？ 以ui-router为例，看看ng如何解决这个问题：
1. 事件方案：  ui-router通过$stateChangeStart事件开放对路由切换的控制权,因为需要一个相对集中的权限控制点，所以写再run回调中：
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
2. resolve方案：