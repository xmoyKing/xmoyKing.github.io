---
title: AngularJS巩固实践-45-依赖注入$injector
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - $injector
date: 2017-09-01 08:27:40
updated:
---

依赖注入，是一种软件设计模式原则，即DIP（依赖倒置原则），描述组件之间高层组件不应该依赖于底层组件，依赖倒置是指实现和接口倒置，采用自顶向下的方式关注所需的底层组件接口，而不是其实现。

#### $injector的创建
bg的依赖注入能力来自$injector服务，在ng启动时最先创建的对象之一，不管是ng通过ngApp指令自启动还是手动调用angular.bootstrap方法启动，都会转到bootstrap方法中。

首先创建的$injector对象存放在DOM节点上，所以在一个DOM节点上只能启动一次，可以通过element.injector()判断。

首次加载会根据传入的业务module，然后ng会追加$rootElement配置方法和ng模块依赖，最后创建$injector对象，然后利用$injector对象的invoke方法启动依赖注入，并立即执行当前阶段的compile处理。
```js
function bootstrap(element, modules){
  // ...
  var doBootstrap = function(){
    element = jqLite(element);

    if(element.injector()){
      var tag = (element[0] === document) ? 'document' : startingTag(element);

      // 将尖括号编码#8683防止输入被转义为空字符串
      throw ngMinErr(
        'btstrpd',
        'App Already Bootstrapped with this Element "{0}"',
        tag.replace(/</,'&lt;').replace(/>/,'&gt;')
        );
      }

      modules = modules || [];
      modules.unshift(['$provide', function($provide){
        $provide.value('$rootElement', element);
      }]);
      modules.unshift('ng');

      var injector = createInjector(modules);
      injector.invoke(['$rootScope', '$rootElement', '$compile', '$injector', '$animate',
      function(scope, element, compile, injector, animate){
        scope.$apply(function(){
          element.data('$injector', injector);
          compile(element)(scope);
        });
      }]);
      return injector;
  };

  // ...
}
```
createInjector方法来自injector.js，它会先根据传入的module信息，依次invoke创建所有$provide服务的实例，下面代码能看出它利用provider.$get方法来获得服务实例的对象，对于ng中可注入的对象，都需要提供$get方法，是$injector对象创建可注入实例的入口。常见的Value、Factory、Service、Provider等服务都提供了$get方法，它们都是Provider服务的简化语法糖。
```js
function createInjector(modulesToLoad){
  // ...
  instanceCache = {},
  instanceInjector = (
    instanceCache.$injector = createInternalInjector(instanceCache, function(servicename){
      var provider = providerInjector.get(servicename + providerSuffix);
      return instanceInjector.invoke(provider.$get, provider);
    })
  );

  forEach(loadModules(modulesToLoad), function(fn){
    instanceInjector.invoke(fn || noop);
  });

  return instanceInjector;
}
```

#### $injector注入方式
ng中依赖注入的注入方式有3种:，数组内联式注入，以及$inject标记式注入：
```js
// 按名推断式注入
angular.service('domeService', function($window){
  // ...
})

// 数组内联式注入
angular.service('domeService', ['$window',function($window){
  // ...
}])

// $inject标记式声明注入
var domeService = function($window){
  // ...
};
demoService.$inject = ['$window'];
angular.service('domeService', domeService);
```

从ng的源码$injector.annotate方法可知他们是如何工作的：
```js
var FN_ARGS = /^function\s*[^\(]*\(\s*([^\)]*)\)/m;
var FN_ARGS_SPLIT = /,/;
var FN_ARG = /^\s*(_?)(.+?)\1\s*$/;
var STRIP_COMMENTS = /((\/\/.*$)|(\/\*[\s\S]*?\*\/))/mg;

function annotate(fn){
  var $inject,
      fnText,
      argDecl,
      last;

  if(typeof fn == 'function'){
    if(!($inject = fn.$inject)){
      $inject = [];
      fnText = fn.toString().replace(STRIP_COMMENTS, '');
      argDecl = fnText.match(FN_ARGS);
      forEach(argDecl[1].split(FN_ARG_SPLIT), function(arg){
        arg.replace(FN_ARG, function(all, underscore, name){
          $inject.push(name);
        });
      });
      fn.$inject = $inject;
    }
  }else if(isArray(fn)){
    last = fn.length - 1;
    assertArgFn(fn[last], 'fn')
    $inject = fn.slice(0, last);
  }else{
    assertArgFn(fn, 'fn', true);
  }

  return $inject;
}
```
在$injector服务实例化特定服务之前，首先会调用这段annotate方法来解析服务的依赖，如果是function对象声明，则会先检查function是否具有$inject属性，如果存在，则就是$inject标记式声明注入，直接返回$inject的依赖声明。

否则就是按名推断式注入，则ng利用toString将该function变成字符串，然后利用正则匹配出所需依赖参数，并缓存在fn.$inject之上。

若是数组对象，则为数组内联式注入，利用Array.slice取出除了最后一个function外的所有依赖声明。

annotate的源码利用了正则和replace函数，快捷的使用，但这样使用正则的方式不推荐，因为在项目上线时常常需要混淆处理js代码，然后参数名经常会被变为一些无意义的短名，此时按名推断式注入就不能正常工作了。解决方案是使用ngAnnotate、ngMin这类注入插件帮助修复依赖注入的方式。

但更好的方式是用数组内联式注入和$inject标记式声明注入，其中，数组式更简洁，同时不会阻断链式API的书写方式。

#### $injector妙用
在某些场景中，可以注入$injector服务，然后手动调用get方法获取特定服务，如$http拦截器interceptors中注入$http导致的循环依赖，有了$injector服务，能后实现延时注入特定的服务。在获取服务之前也可以利用$injector.has方法来判断是否具有指定的注入实例。

如下是ng源码中演示获取特定Filter的逻辑：
```js
$FilterProvider.$inject = ['$provide'];

function $FilterProvider($provide){
  // ...
  this.register = register;
  this.$get = ['$injector', function($injector){
    return function(name){
      return $injector.get(name + suffix);
    };
  }];
  // ...
}
```
因为返回的是$filter服务，还没有指定特定的Filter名称，所以只能延迟到用户传入Filter名称参数，才利用$injector.get方法返回特定的Filter服务。

若需要临时运行一个函数，同时希望获得依赖注入的能力，那么使用$injector.invoke是一个不错的方式，例如：在SPA应用中，页面title一直会是初始值，不会发生变化，这样不利于SEO或analyze这类Page flow的用户分析，所以应该针对不同的路由设置更有语义的page title。

title组件实现源码：
```js
angular.module('com.ngnice.app').run(function($window, $document, $rootScope, $location, $injector){
  $rootScope.$on('$routeChangeSuccess', function(event, current){
    if(current && (current.$$route || current).redirectTo){
      return;
    }

    var title = getPageTitle(current);
    $window.title = title;
    $document.title = title;
  });

  function getPageTitle(current){
    var title = current.$$route.title;
    if(!title){
      return $window.title;
    }
    return angular.isString(title) ? title : $injector.invoke(title);
  }
});
```
路由title设置代码：
```js
angular.module('com.ngnice.app').config(function($routeProvider){
  $routeProvider.when('/order', {
    templateUrl: 'views/order.html',
    controller: 'OrderController',
    title: 'Order list'
  });
  $routeProvider.when('/order/:id', {
    templateUrl: 'views/orderDetails.html',
    controller: 'OrderDetailsController',
    title: ['$routeParams', function($routeParams){
      return 'Order of ' + $routeParams.id;
    }]
  });
});
```
通过监听ng路由改变事件$routeChangeSuccess, 当路由切换成功时，根据当前路由信息获取$routeProvider路由配置信息中配置的title信息来设置页面的title。

若配置的title信息是字符串，则直接将字符串设置window和document的title，若需要动态获取订单变化，则title需要获得ng依赖注入的能力。此时$injector.invoke能提供这个功能，它能让执行方法在运行时获得依赖注入的能力，并被执行，然后返回执行结果。

$injector.invoke方法同样支持按名推断式注入，数组内联式注入，以及$inject标记式声明注入三种方式。