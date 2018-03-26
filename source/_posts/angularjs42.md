---
title: AngularJS巩固实践-42-ng中的Ajax数据格式转换
categories:
  - AngularJS
tags:
  - AngularJS
  - ajax
date: 2017-08-27 08:24:42
updated:
---

ng作为前端应用框架，一般默认的最佳实践是基于ajax调用来与远程服务交互的单页面SPA（Single Page Application）架构。

在ng中，内置了$http和$resource来处理ajax请求，他们都是默认基于JSON的数据传递方式，$http在ng开发中作为最基础的ajax请求服务，能够处理所有的ajax请求，而$resource则在$http基础上，专门为简化RESTful风格而设计，使用面比$http更窄更专，ng推荐是使用RESTful风格。

#### 兼容老式API
有的时候，由于某些原因，不得不使用非JSON请求方式的服务，比如Form表单提交、XML、或其他自定义的数据格式，那么此时，该如何使用这类API？

ng在$http服务中提供了对request/response数据的处理和扩展机制，有两种方式实现对ajax的拦截修改，分别是请求参数配置和拦截器，对于兼容老式API的要求，推荐利用请求参数配置方法修改ajax的传输数据格式。

以常规Form表单的POST请求为例，其他格式原理类似，对于这类API，按照API的数量范围分为两种情况：
1. 仅个别API需要转换数据格式
2. 整个API都需要转换数据格式

##### 部分ajax调用的request设置
ng在$http的ajax服务方法中提供了多个参数的重载方式，最后一个缺省参数就是ajax的配置信息，以Post方法为例。

`post(url, data, [config])`中三个参数分别是请求的URL、post传递的body数据、可选的ajax配置信息。

对于配置信息，在ng中主要包括如下属性：
- method：请求的HTTP Method，如GET、POST、PUT、DELETE等
- url： 请求的URL
- params: URL查询数据，可以传入一个JS对象，ng会将其参数化为`?key1=value1&key2=value2`形式，并作为查询字符串拼接在URL后面
- data： 需要发送到服务端的数据
- headers: 请求的HTTP Header信息
- xsrfHeaderName: 携带XSRF Token的header名称，ng为了防止XSRF攻击而提供的解决方案
- xsrfCookieName: 携带XSRF Token的cookie名称
- transformRequest： 对于ajax请求转换函数，可以传入一个或一组回调函数，多个回调会像UNIX管道一样被依次传递执行
- transformResponse：和transformRequest的使用方式相同，但不同的是它处理的是ajax的响应数据
- cache: ajax请求的数据缓存，可传入true或$cacheFactory的缓存对象，若为true则使用默认的缓存对象缓存。ng对于HTML视图模板的请求会自动缓存在$templateCache中，依次减少远程服务器的负荷
- timeout: 设置请求的超时时间
- withCredentials: 可选的HTTP认证设置
- responseType： ajax请求响应类型，包括json、text、arraybuffer等

注：$http.post等都是针对特定method的简化写法，将url、data参数抽离为对应方法的参数，$http.post源码如下：
```js
function createShortMethodsWithData(name){
  forEach(arguments, function(name){
    $http[name] = function(url, data, config){
      return $http(extend(config || {}, {
        method: name,
        url: url,
        data: data
      }));
    };
  });
}

// ...

createShortMethodsWithData('post', 'put')
```
$http.post的最后一个配置参数中可以指定transforRequest属性，允许在ajax请求发送数据之前，自定义请求数据的格式转换方法,使用如下：
```js
$http.post('/url', {
  id: 1,
  name: 'king'
}, {
  transformRequest: function(request){
    return $.param(request); // 通过jquery的$.param方法进行表单提交数据的格式转换
  }
});
```

##### 全局ajax调用的请求配置
ng提供了全局的默认配置属性，在$httpProvider.defaults之上包含所有ajax请求的默认数据转换格式，可以在config阶段注入$httpProvider,并修改默认全局配置，包括transformResponse、transformRequest、headers、xsrfCookieName、xsrfHeaderName。

对整个系统的API格式转换，通过对$http请求的全局配置，就不再需要为每个http请求传递transformRequest参数了：
```js
angular.module('com.ngnice.app').config(function($httpProvider){
  $httpProvider.defaults.transformRequest = [
    function(request){
      return $.param(request);
    }
  ];
});

// 使用
$http.post('/url', {
  id: 1,
  name: 'king'
});
```
也可以创建自定义的response转化，比如在JSON字符串之前加入自定义的前缀，来防止JSON Array攻击。

##### ajax请求配置的源码分析
$httpProvider.defaults的实现源码：
```js
function $HttpProvider(){
  var JSON_START = /^\s*(\[|\{[^\{])/,
      JSON_END = /[\}\]]\s*$/,
      PROTECTION_PREFIX = /^\)\]\}',?\n/,
      CONTENT_TYPE_APPLICATION_JSON = {
        'Content-Type': 'application/json;charset=utf-8'
      };
  var defaults = this.defaults = {
    transformResponse: [function(data){
      if(isString(data)){
        data = data.replace(PROTECTION_PREFIX, '');
        if(JSON_START.test(data) && JSON_END.test(data))
          data = fromJson(data);
      }
      return data;
    }],

    transformRequest: [function(d){
      return isObject(d) && !isFile(d) && !isBlob(d) ? toJson(d) : d;
    }],

    headers: {
      common: {
        'Accept': 'application/json, text/plain, */*'
      },
      post: shallowCopy(CONTENT_TYPE_APPLICATION_JSON),
      put: shallowCopy(CONTENT_TYPE_APPLICATION_JSON),
      patch: shallowCopy(CONTENT_TYPE_APPLICATION_JSON),
    },

    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN'
  };
  // ...
}
```
上述代码中，$httpProvider.defaults定义了默认的transformResponse、transformRequest，以及通用的HTTP Headers和post、put、patch配置信息，这些全局配置信息的修改将会对所有的ajax请求产生影响，包括$http、$resource。

ng内部$http服务会大量使用这些默认配置，同时实现自定义特定的配置信息：
```js
function $http(requestConfig){
  // ...
  var config = {
    method: 'get',
    transformRequest: defaults.transformRequest,
    transformResponse: defaults.transformResponse
  };
  var headers = mergeHeaders(requestConfig);

  extend(config, requestConfig);

  config.headers = headers;
  config.method = uppercase(config.method);

  var serverRequest = function(config){
    headers = config.headers;
    var reqData = transformData(config.data, headersGetter(headers), config.transformRequest);

    if(isUndefined(reqData)){
      forEach(headers, function(value, header){
        if(lowercase(header) === 'content-type'){
          delete headers[header];
        }
      });
    }

    if(isUndefined(config.withCredentials) && !isUndefined(defaults.withCredentials)){
      config.withCredentials = defaults.withCredentials;
    }

    return sendReq(config, reqData, headers).then(transformResponse, transformResponse);
  }

  // ...
}

function transformData(data, headers, fns){
  if(isFunction(fns))
    return fns(data, headers);

  forEach(fns, function(fn){
    data = fn(data, headers);
  });

  return data;
}

function transformResponse(response){
  var resp = extend({}, response, {data: transformData(response.data, response.headers, config.transformResponse)});

  return (isSuccess(response.status)) ? resp : $q.reject(resp);
}

function mergeHeaders(config){
  var defHeaders = defaults.headers,
      reqHeaders = extend({}, config.headers),
      defHeaderName, lowercaseDefHeaderName, reqHeaderName;

  defHeaders = extend({}, defHeaders.common, defHeaders[lowercase(config.method)]);

  defaultHeadersIteration:
    for (defHeaderName in defHeaders){
      lowercaseDefHeaderName = lowercase(defHeaderName);

      for(reqHeaderName in reqHeaders){
        if(lowercase(reqHeaderName) === lowercaseDefHeaderName){
          continue defaultHeadersIteration;
        }
      }

      reqHeaders[defHeaderName] = defHeaders[defHeaderName];
    }

  execHeaders(reqHeaders);
  return reqHeaders;

  function execHeaders(headers){
    // ...
  }
}
```
首先，在$http服务中ng会加载默认配置，包括method、transformRequest、transformResponse配置信息。

然后，利用默认值defaults.headers创建默认的HTTP Header配置信息，在继续利用angular.extend合并用户自定义的特定配置信息，实现针对特定$http请求的用户自定义配置，最后在ajax请求中设置合并后的header属性。

接着，发送ajax请求，在发送ajax请求之前$http会利用config.transformRequest对ajax请求数据进行格式转化，以及会对ajax调用返回的响应数据进行拦截转换（此处利用Promise的then注册回调方法：sendReq(config，reqData，headers).then(transformResponse, transformResponse)）。

ajax配置中的transformRequest、transformResponse是一组数据转换函数，$http会依次调用他们，并把前一个转换函数的返回值继续传递到下一个转换函数，这样就形成了数据的管道式转换。

transformRequest、transformResponse转换函数是一组普通的js函数，这些函数接收需要转换的数据和请求/响应的HTTP Header作为输入参数，并且以转化后的数据作为返回值。注：由transformData函数可知，transformRequest、transformResponse可以是单个函数，也可以是一组转换函数的数组集合。

$http中的transformRequest、transformResponse转换函数是ng提供对ajax请求数据和响应数据转换的最佳切入点，此时可自定义实现特定数据格式的转换，如Form表单数据格式的转换，以及数据安全方面的策略等。

ng在内部也提供了解决JSON安全的策略（关于JSON安全参见[JSON维基百科](https://en.wikipedia.org/wiki/JSON)），$http默认服务端返回的JSON字符串以`")]}',\n"`作为前缀，$http会自动去掉整个前缀再解析JSON字符串,所以，如下JSON字符串在ng中是合法的`")]}',\n{\"name\":\"king\"}"`

在$http源码中函数定义开始处，整个JSON保护字符串就定义了，同时在默认的transformResponse转换函数中也包含了整个保护字符串。