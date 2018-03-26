---
title: AngularJS巩固实践-41-Angular中的AOP机制
categories:
  - AngularJS
tags:
  - AngularJS
  - AOP
date: 2017-08-25 20:01:54
updated:
---

在软件设计中，AOP时Aspect-Oriented Programming的缩写，即面向切面编程/切片编程。指通过编译时（Compile)置入代码，运行时（Runtime）动态代理，以及框架提供管道式执行等策略实现程序通用功能与业务模块的分离，统一处理、维护的一种解耦设计。

AOP式OOP的延续，是软件开发的一种设计方式，也是很多服务端框架（Spring）中的核心内容之一，是函数式编程的一种衍生范型。利用AOP可以对业务逻辑的各个部分进行隔离，从而降低业务逻辑各部分之间的耦合度，提高程序的可重用性，同时提高开发效率。

AOP使用的场景主要是：权限控制、日志模块、事务处理、性能统计、异常处理等独立、通用的非业务模块。

在ng中同样内置了一些AOP的设计思想，便于实现程序通用功能与业务模块的分离、解耦、统一处理和维护。$http中的拦截器（interceptors)和装饰器（$provide.decorator）就是ng中的AOP切入点。前者以管道式执行策略实现，后者通过运行时动态代理实现。

#### 拦截器案例
从一个简单案例触发，理解ng拦截器的应用场景。

假设项目采用RESTful架构风格，倾向于无状态的服务设计，但又希望在ng中引入基于token的访问控制方案（在服务端设计中，token可以存在在MemCache这类内存NoSQL数据库中）。这意味着，在ng中，每次ajax请求都需要在HTTP Header中附带上token字段，假设该token为`ng-demo-token`。

ng中的拦截器能够实现对所有ajax请求拦截和切入，分为4个切入点：
1. 发起请求Request之前切入
2. 请求Request错误时切入
3. 请求响应成功时切入
4. 请求响应失败时切入

这四个切入点可以多个同时使用，只需将他们分别以request，requestError，response，responseError为key存放在一个object对象上，并追加在$httpProvider.interceptors的数组队列中即可。其实，最好的方式时将切入逻辑定义在ng的Factory服务中，这样便于业务分离和逻辑复用：
```js
$provide.factory('myHttpinterceptor', function($q){
  return {
    // 可选方法
    rquest: function(config){
      // 成功后 do something
      return config;
    },

    // 可选方法
    requestError: function(rejection){
      // 出错后 do something
      return $q.reject(rejection);
    },

    // 可选方法
    response: function(response){
      // 成功后 do something
      return response;
    },

    // 可选方法
    responseError: function(rejection){
      // 出错后 do something
      return $q.reject(rejection);
    }
  };
});

$httpProvider.interceptor.push('myHttpInterceptor');
```

如下代码来自[green.auth](https://github.com/greengerong/green-auth/blob/master/src/green.auth.js)中关于token设置的一段：
```js
angular.module("green.auth", [])
.factory("authInterceptor", ["$q", "authService",
  function($q, authService) {
    return {
      "request": function(config) {
        config.headers = config.headers || {};
        var token = authService.getToken() || {};
        angular.forEach(token, function(value, key) {
          if (!config.headers[key]) {
            config.headers[key] = value;
          }
        });
        return config || $q.when(config);
      }
    };
  }
]).constant("tokenCacheFactory", {
    "jsObject": function() {
      var tokenStorage;
      return [function() {
        return {
          save: function(token) {
            tokenStorage = angular.copy(token);
            return tokenStorage;
          },
          get: function() {
            return tokenStorage;
          },
          remove: function() {
            tokenStorage = null;
          }
        }
      }];
    },
    "localStorage": function(storageKey) {
      return ["$window", function($window) {
        return {
          save: function(token) {
            $window.localStorage.setItem(storageKey, angular.toJson(token));
            return token;
          },
          get: function() {
            var tokenStr = $window.localStorage.getItem(storageKey);
            return tokenStr ? angular.fromJson(tokenStr) : null;
          },
          remove: function() {
            $window.localStorage.removeItem(storageKey);
          }
        }
      }]
    },
    "sessionStorage": function(storageKey) {
      return ["$window", function($window) {
        return {
          save: function(token) {
            $window.sessionStorage.setItem(storageKey, angular.toJson(token));
            return token;
          },
          get: function() {
            var tokenStr = $window.sessionStorage.getItem(storageKey);
            return tokenStr ? angular.fromJson(tokenStr) : null;
          },
          remove: function() {
            $window.sessionStorage.removeItem(storageKey);
          }
        };
      }];
    },
    "cookie": function(storageKey) {
      return ["$cookieStore", function($cookieStore) {
        return {
          save: function(token) {
            $cookieStore.put(storageKey, angular.toJson(token));
            return token;
          },
          get: function() {
            var tokenStr = $cookieStore.get(storageKey);
            return tokenStr ? angular.fromJson(tokenStr) : null;
          },
          remove: function() {
            $cookieStore.remove(storageKey);
          }
        };
      }];
    }
  }).provider('authService', function() {
    var tokenCache, cacheFactory, self = this;

    self.setCacheFactory = function(factory) {
      cacheFactory = factory;
      return self;
    };

    self.$get = ['tokenCacheFactory', "$injector",
      function(tokenCacheFactory, $injector) {
        cacheFactory = cacheFactory || tokenCacheFactory.jsObject();
        tokenCache = $injector.invoke(cacheFactory);
        return {
          setToken: function(token) {
            return tokenCache.save(token);
          },
          getToken: function() {
            return tokenCache.get();
          },
          removeToken: function() {
            return tokenCache.remove();
          }
        };

      }
    ];
  }).config(['$httpProvider',
    function($httpProvider) {
      $httpProvider.interceptors.push('authInterceptor');
    }
  ])
```
首先建议一个包含拦截器Request的处理函数，它会调用authService.getToken方法获取token配置，并加入header，以便ajax传递到服务端做进一步的访问控制，在config阶段，利用注入的$httpProvider服务将刚才定义的Request拦截器追加到ng的默认拦截器上:`$httpProvider.interceptors.push('authInterceptor');`

这样就实现了对ajax请求的拦截注入token信息，另外，同时实现的tokenCacheFactory包含isObject, localStorage, sessionStorage, cookie几种存储token的方式。
使用方式如下：
```js
angular.module("green.auth.demo", ["green.auth", "ngCookies"])
	.config(["tokenCacheFactory", "authServiceProvider",
		function(tokenCacheFactory, authServiceProvider) {
	      //TODO: you can define your token cache. default is in js object.
	      //tokenCacheFactory inlcude : jsObject, localStorage, sessionStorage, cookie
	      authServiceProvider.setCacheFactory(tokenCacheFactory.cookie("my-customer-stroage-token-key"));
		}
	])
  .controller('DemoCtrl', function($http, authService, $scope){
    $scope.setToken = function(){
      var token = $scope.token ? {
        'ng-demo-token': $scope.token
      } : {};

      authService.setToken(token);
    };
  });
```
在实际使用中，设置token的代码应该放在登录成功或首页controller加载的resolve等位置。

#### 拦截器源码分析
上述案例了解如何使用ng的拦截器，解析来对拦截器源码进行分析：
```js
var interceptorFactories = this.interceptors = [];

var responseInterceptorFactories = this.responseInterceptors = [];

  this.$get = ['$browser', '$httpBackend', '$$cookieReader', '$cacheFactory', '$rootScope', '$q', '$injector',
      function($browser, $httpBackend, $$cookieReader, $cacheFactory, $rootScope, $q, $injector) {

    var defaultCache = $cacheFactory('$http');

    var reversedInterceptors = [];

    forEach(interceptorFactories, function(interceptorFactory) {
      reversedInterceptors.unshift(isString(interceptorFactory)
          ? $injector.get(interceptorFactory) : $injector.invoke(interceptorFactory));
    });

    forEach(responseInterceptorFactories, function(interceptorFactory, index) {
      var responseFn = isString(interceptorFactory) ? $injector.get(interceptorFactory) : $injector.invoke(interceptorFactory);


      reversedInterceptors.splice(index, 0, {
        response: function(response){
          return responseFn($q.when(reponse));
        },
        reponseError: function(reponse){
          return responseFn($q.reject(reponse));
        }
      });
    });

    // ...

    function $http(requestConfig) {
      // ...
      var chain = [serverRequesr, undefined];
      var promise = $q.when(config);

      // apply interceptors
      forEach(reversedInterceptors, function(interceptor) {
        if (interceptor.request || interceptor.requestError) {
          requestInterceptors.unshift(interceptor.request, interceptor.requestError);
        }
        if (interceptor.response || interceptor.responseError) {
          responseInterceptors.push(interceptor.response, interceptor.responseError);
        }
      });

      while(chain.length){
        var thenFn = chain.shift();
        var rejectFn = chain.shift();

        promise = promise.then(thenFn, rejectFn);
      }

      promise.success = function(fn){
        promise.then(function(response){
          fn(response.data, reponse.status, response.headers, config);
        });
        return promise;
      };

      promise.error = function(fn){
        promise.then(function(response){
          fn(response.data, reponse.status, response.headers, config);
        });
        return promise;
      };

      return promise;
    };

    // ...
```
先声明interceptors和responseInterceptors两个数组，他们时所有拦截器的集合，其中reponseInterceptors时interceptors对ajax请求的简化方式，所以，若只是针对reponse的拦截，可以使用如下方式注册：
```js
$provide.factory('myHttpInterceptor', function($q){
  return function(promise){
    return promise.then(function(response){
      // 成功 do some...
      return response;
    }, function(response){
      // 失败 do some...
      return $q.reject(response);
    });
  };
});

$httpProvider.responseInterceptors.push('myHttpInterceptor');
```
利用$httpProvider.interceptors或$httpProvider.responseInterceptors注册的拦截函数，可以是一个Provider服务的名称或一个可注入的函数，甚至是一个可注入的数组。对于字符串方法，ng会利用$injector.get(interceptorFactory)在运行时获取该服务，而针对后两种方式，ng会利用$injector.invoke在运行时创建该对象。一般建议使用Factory方式定义拦截器，并用字符串方式push到$httpProvider.interceptors或$httpProvider.reponseInterceptors，因为这样有更好的逻辑分离和复用。

紧接在$get方法中，ng将interceptors和reponseInterceptors反转合并到一个reversedInterceptors的拦截器内部变量中保存，最后在$http函数中以[serverRequest, undefined]为中心，serverRequest是ajax请求的promise操作，将reversedInterceptors中的所有拦截器函数依次加入chain链式数组中，若是request或requestError，就放在链式数组起始位置，相反，response或responseError，就放在链式数组尾部位置。

需要注意的是，在chain中添加的都是成对的request/requestError或response/responseError，即使只有一个，另外一个也必须是undefined。就行chain数组声明那样。后面的代码将利用Promise机制注册这些拦截器函数，实现管道式AOP拦截机制。

在Promise中需要两个函数来注册回调，分别是成功和失败回调，为了能在接下来的循环中简介的注册Promise回调函数，所有都是成对的添加到chain中。

这些被注册的拦截器链，通过$q.when(config)构造Promise启动，它会先传入$http的config对象，并执行所有的request拦截器，依次再到serverRequest这个ajax请求，此时将挂起后面所有的response拦截器，直到ajax请求响应完成，再次执行剩下的reponse回调。若在request过程中出现异常失败则执行后面的requestError回调。responseError与response类似。

最后定义的success和error方法，是ng提供的Promise的便捷写法。

#### ng中的装饰器
假设需要在项目中引入一个第三方的foo服务，其定义如下：
```js
angular.module('com.ngnice.app').factory('foo', function(){
  return {
    name: 'Angular',
  };
});

angular.module('com.ngnice.app').controller('DemoCtrl', function(foo){
  var vm = this;
  console.log(foo.greet());
  return vm;
});
```
却发现foo服务少了需要的greet API，此时该如何办？放弃foo服务么，还是联系提供者者修改添加API。若以上都不行，那么就需要我们自己添加这个API，但不能影响到原foo服务的已有代码，此时就可以利用ng的装饰器来装饰foo服务。
```js

angular.module('com.ngnice.app').config(function($provide){
  $provide.decorator('foo', function($delegate){
    $delegate.greet = function(){
      return 'hello, ' + this.name;
    };
  });

  return $delegate;
});
```
$provide服务是ng内部用于创建所有Provider服务的服务对象，可以在ng的config阶段注入并使用，此时就可以利用$provide来装饰其他对象，$provide中提供了decorator的装饰函数，运行装饰修改其他的服务，它接收所需要装饰的服务的名称和对此服务的装饰函数，装饰函数的参数$delegate代表需要装饰的服务实例。

装饰器不仅可用在对第三方服务的扩展，而且可以做到对服务进行通用处理，如日志记录、访问控制、性能测试等，此处推荐一个JS AOP处理框架： [aopjs](https://github.com/victorcastroamigo/aopjs)。

#### 装饰器源码分析
装饰器的实现很简单，在装饰器调用时，先取出服务的Provider对象（在config阶段还没有实例，此时只有服务的Provider对象存在），并缓存其$get方法（$get方法是ng创建服务实例的入口函数）。然后其$get方法会被替换为新的匿名函数，在新函数中先创建原来的服务实例，再以$delegate为参数传入装饰函数，从而实现对服务的修改和拦截。
```js
function decorator(serviceName, decorFn){
  var origProvider = providerInjector.get(serviceName + providerSuffix),
      orig$get = origProvider.$get;

  origProvider.$get = function(){
    var origInstance = instanceInjector.invoke(orig$get, origProvider);

    return instanceInjector.invoke(decorFn, null, {$delegate: origInstance});
  };
}
```
注：装饰器对ng的常量Constant不可用，因为Constant是不可变的，它在定义时以及确定了服务实例，并不存在运行时的$get函数。而其他的Provider服务则可被装饰。