---
title: AngularJS巩固实践-42-过滤器Filter
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - filter
  - AngularJS深度剖析
date: 2017-08-29 19:55:30
updated:
---

过滤器Filter是对视图模板中变量的格式化利器，它接收一组输入（格式化变量和格式化参数）并得到一个特定输出，过滤器利用`|`作为分隔符，可以用类似UNIX管道的语法，形成输入、输出的连续传递。

同时，过滤器也是一种特殊的服务，在ng中所有的过滤器都会以名称加上"Filter"后缀为服务名称注册成一个服务，同时，还能通过$filter服务访问整个Filter对象，因此，可以在Controller或Service这类代码中注入相应的Filter服务来重用它，还可以注入$filter服务，通过Filter名称获取指定的Filter对象。

#### 复用Filter
在某些场景下，Controller或Service需要对数据进行货币格式的转换，首先可以利用ng内置的过滤器：
```js
angular.module('com.ngnice.app').controller('DemoController', function(currencyFilter){
  var vm = this;
  console.log(currencyFilter(221.11)); //输出 $221.11
  return vm;
});
```
在Controller中添加对currencyFilter的依赖，使得Controller能获取currencyFilter过滤器的实例，并调用该方法对货币进行格式化，这样就可以在Controller中快速重用ng内置的过滤器了，简化代码逻辑。

#### 重用多个Filter
有的时候需要用到多个ng内置的或自定义的过滤器，也可以如上依次注入所有的过滤器即可，但那样会让Controller的声明变得非常冗长，此时可以考虑注入$filter，在Controller中通过名称获取过滤器。
```js
angular.module('com.ngnice.app').filer('fullName', function(){
  return function(user){
    return user.firstName + ' ' + user.lastName;
  };
});

angular.module('com.ngnice.app').controller('DemoController', function($filter){
  var vm = this;

  console.log($filter('currency')(221.11));
  console.log($filter('numver')(221.11));
  console.log($filter('fullName')({
    firstName: 'king'，
    lastName: 'xmoy'
  }));

  return vm;

});
```

#### Filter源码分析
如下是Filter的源码：
```js
$FilterProvider.$inject = ['$provide'];

function $FilterProvider($provide){
  var suffix = 'Filter';

  function register(name, factory){
    if(isObject(name)){
      var filters = {};
      forEach(name, function(filter, key){
        filters[key] = register(key, filter);
      });
      return filters;
    }else{
      return $provide.factory(name + suffix, factory);
    }
  }
  this.register = register;

  this.$get = ['$injector', function($injector){
    return function(name){
      return $injector.get(name + suffix);
    };
  }];

  // ...
```
$filter也是一个服务，它需要注入$provide并初始化：
```js
$provide.provider({
  // ...
  $filer: $FilterProvider,
  // ...
});
```
同时过滤器的register方法支持两种方式的注册：
1. 传入键值对像，ng循环该对象，并注册每一项过滤器的同时将没一项Filter缓存在$filter服务上：
```js
register({
  'currency': currencyFilter,
  'date': dateFiler,
  // ...
});
```
2. 传入过滤器名称和过滤器Factory函数，这种方式下，ng会利用$provide提供的Factory方法以过滤器名加上'Filter'后缀注册成服务：
```js
register('currency', currencyFilter);
register('date', dateFilter);
// ...
```

由上可知，ng在注册过滤器时，利用$provide的Factory方法将过滤器注册为服务，同时在$filter服务上缓存该服务对象，所以可以在Controller、Service中注入特定的过滤器或通过$filter服务来复用这些过滤器