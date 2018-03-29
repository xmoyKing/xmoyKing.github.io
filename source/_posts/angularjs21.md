---
title: AngularJS入门笔记-21-Ajax和Promise
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - promise
date: 2017-06-28 10:24:24
updated:
---

ajax是现代web应用的基础，当需要让浏览器加载新内容而不刷新页面时就需要用到ajax，ng内置了ajax请求和异步promises。
- 发起ajax请求 使用$http服务
- 接收ajax请求响应 使用success，error或then方法在$http方法返回的对象上注册回调函数
- 处理非json数据，若是xml可使用jqLite处理
- 请求或预处理响应配置 使用转换函数
- 默认ajax配置 使用$httpProvider
- 拦截请求或响应 使用$httpProvider注册拦截器工厂函数
- 表示在未来某刻的活动 使用promises，由deferred对象和promises对象组成
- 获取deferred对象 调用由$q服务提供的defer方法
- 获取promises对象 使用由deferred对象定义的promise值
- 链接所有promises 使用then方法注册回调，then返回另一个promises，当回调函数被执行时该promises将被resolved
- 等待多个promises 使用$q.all方法创建promises，所有promises被resolved后整体才会resolved

### ajax
一般通过ng内置的$http服务发起ajax请求，它是被异步执行的标准http请求（这也是$http服务名称的来源吧）。ajax异步刷新能向后台请求内容和数据，创建富客户端的重要手段。

使用jquery中的ajax与ng中的ajax差别就是，ng中将从服务器获取的数据应用到作用域中会自动更新所有绑定，而jquery中需要显示处理数据，并且操纵dom。

$http服务产生请求有两种方法，一种是使用如下的一些快捷方法：
- get(url, config) 执行GET请求
- post(url, data, config) 执行POST请求
- delete(url, config) 执行DELETE请求
- put(url, data, config) 执行PUT请求
- head(url, config) 执行HEAD请求
- jsonp(url, config) 执行跨域js请求,JSONP（JSON with Padding表示JSON和填充）能绕过浏览器对js代码被载入的限制的工作方式。
另一个是将$http服务对象当做函数传入配置对象。
```js
angular.module("exampleApp", [])
.controller("defaultCtrl", function ($scope, $http) {
    $scope.loadData = function () {
        $http.get("productData.json").success(function (data) {
            $scope.products = data;
        });
    }
});
```


#### GET和POST，应该如何选择
一般的经验是GET请求仅被用于所有只读的信息检索，而POST请求被用于改变应用状态的操作，所以GET是安全的，除了检索它没有任何副作用，而POST是不安全的（有可能会改变一些东西）。
同时GET请求是可寻址的，即所有信息都被包含在URL中，所以它适合放入书签，链接这些地址。GET请求不应该用于改变状态。若该GET请求是一个删除操作，因为url很有可能会被爬虫访问，而此时则会造成很大的破坏。

#### 接收响应
发起ajax请求只是第一部分，当它响应时，我们需要接收响应，对返回的数据进行处理，由$http服务方法返回的承诺对象所定义的方法：
- success(fn) 当http请求完成时，调用fn
- error(fn) 当http请求无法完成时，调用fn
- then(fn1, fn2) 注册成功函数fn1和失败函数fn2
success和error方法接收简化后的响应，success接收服务器正常返回数据，error接收问题的描述字符串。
then方法提供了更详细的响应信息，如下是then传入处理函数的对象的属性
- data 请求返回数据
- status 返回http状态码
- headers 返回指定的http头部信息
- config 配置对象，该对象是在发起ajax前配置
```js
$http.get("productData.json").then(function (response) {
    console.log("Status: " + response.status);
    console.log("Type: " + response.headers("content-type"));
    console.log("Length: " + response.headers("content-length"));
    console.log(response.config);
    $scope.products = response.data;
});
```
![then](1.png)
使用then方法时，ng自动处理json数据

#### 配置ajax请求
由$http服务定义的方法都接收一个可选参数，即配置对象，绝大多数情况下使用默认配置即可，但也可显式设置。
- data 设置发送到服务器的数据，若设置了该值，则会被ng序列化为JSON格式
- headers 用于设置请求头部
- method 设置请求所使用的HTTP方法
- params 用于设置URL属性
- timeout 设置请求过期时间
- transformRequest 用于在请求发送到服务器前操作数据
- transformResponse 用于在请求发送到服务器后操作数据
- url 设置请求url
- withCredentials 为true时，表示底层浏览器请求对象上的withCredentials选项可用，包含在请求中验证cookie
- xsrfHeaderName,xsrfCookieName 这些属性用来防跨域请求伪造攻击
在ng中，内置的转换就是将传出的数据序列化为JSON，传入的JSON解析成js对象。
将配置对象上的transformRequest属性来转换响应，该函数负责返回更换后的数据，通常是发送给服务器的反序列化版本，比如若与服务器约定的格式是xml格式，那么将请求转换为xml，将响应转换为json。
```js
// 获取xml响应
$scope.loadData = function () {
    var config = {
        transformResponse: function (data, headers) {
            if(headers("content-type") == "application/xml"
                    && angular.isString(data)) {
                products = [];
                var productElems = angular.element(data.trim()).find("product");
                for (var i = 0; i < productElems.length; i++) {
                    var product = productElems.eq(i);
                    products.push({
                        name: product.attr("name"),
                        category: product.attr("category"),
                        price: product.attr("price")
                    });
                }
                return products;
            } else {
                return data;
            }
        }
    }

    $http.get("productData.xml", config).success(function (data) {
        $scope.products = data;
    });
}
// 发送xml请求
$scope.sendData = function() {
    var config = {
        headers: {
            "content-type": "application/xml"
        },
        transformRequest: function (data, headers) {
            var rootElem = angular.element("<xml>");
            for (var i = 0; i < data.length; i++) {
                var prodElem = angular.element("<product>");
                prodElem.attr("name", data[i].name);
                prodElem.attr("category", data[i].category);
                prodElem.attr("price", data[i].price);
                rootElem.append(prodElem);
            }
            rootElem.children().wrap("<products>");
            return rootElem.html();
        }
    }
    $http.post("ajax.html", $scope.products, config);
}
```

#### 配置默认ajax
通过$http服务提供器$httpProvider为ajax请求定义默认设置，该提供器定义了一些属性：
- defaults.headers.common 定义用于所有请求的默认头部
- defaults.headers.post 定义用于POST请求的默认头部
- defaults.headers.put 定义用于PUT请求的默认头部
- defaults.transformRequest 定义用于所有请求的转换函数的数组
- defaults.transformResponse 定义用于所有响应的转换函数的数组
- interceptors 拦截器工厂函数数组，拦截器是转换函数的复杂形式
- withCredentials 为所有请求设置withCredentials项，该属性常常用于发起需要验证的跨域请求
defaults.transformRequest和defaults.transformResponse属性是数组，必须用push方法添加。

$httpProvider.interceptors属性是一个数组，插入数组中的每一个元素都有一些属性，而拦截器是转换函数的最佳复杂替代品就是由于这些属性中的request和response。
- request 在产生请求并传入配置对象前调用拦截器函数
- requestError 在上一个请求拦截器抛出错误时调用的拦截器函数
- response 在响应并传入配置对象前调用拦截器函数
- responseError 在上一个响应拦截器抛出错误时调用的拦截器函数
```js
$httpProvider.interceptors.push(function () {
    return {
        request: function (config) {
            config.url = "productData.json";
            return config;
        },
        response: function (response) {
            console.log("Data Count: " + response.data.length);
            return response;
        }
    }
});
```
在上述代码中，在工厂方法产生的对象定义了request和response属性，request拦截器将配置对象的url修改为productData.json,然后返回config对象将其传给下一个拦截器。response拦截器也是这样，不管前面你如何操作/修改，最后都需要返回response对象给下一个拦截器。

### 使用promises
promises是一个对未来发生的事情的注册方式，如ajax请求。promises需要的对象有两个，promise对象用于接收响应的通知，deferred对象用于发送通知。
ng内置的$q服务来获取和管理promises，$q服务定义了一些方法：
- all(promises) 当指定数组中所有promises被解决或其中任一被拒绝时返回promises
- defer() 创建deferred对象
- reject(reason) 返回被拒绝的promises
- when(value) 在被解决的promises中封装一个值作为结果

#### 获取和使用deferred对象
通过$q.defer方法获取deferred对象，该对象有如下一些方法和属性
- resolve(result) 带有指定值的延迟活动完成的信号
- reject(result) 延迟活动失败或由于特定原因将不被完成的信号
- notify(result) 提供来自延迟活动的临时结果
- promise 返回接收其他方法信号的promise对象
基本使用流程是获取deferred对象，然后使用活动结果作为信号调用resolve或reject方法，可选择性通过notify方法提供临时更新。
```html
<html ng-app="exampleApp">
<head>
    <title>Promises</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .directive("promiseWorker", function($q) {
            var deferred = $q.defer();
            return {
                link: function(scope, element, attrs) {
                    element.find("button").on("click", function (event) {
                        var buttonText = event.target.innerText;
                        if (buttonText == "Abort") {
                            deferred.reject("Aborted");
                        } else {
                            deferred.resolve(buttonText);
                        }
                    });
                },
                controller: function ($scope, $element, $attrs) {
                    this.promise = deferred.promise;
                }
            }
        })
        .directive("promiseObserver", function() {
            return {
                require: "^promiseWorker",
                link: function (scope, element, attrs, ctrl) {
                    ctrl.promise.then(function (result) {
                        element.text(result);
                    }, function (reason) {
                        element.text("Fail (" + reason + ")");
                    });
                }
            }
        })
        .controller("defaultCtrl", function ($scope) {

        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well" promise-worker>
        <button class="btn btn-primary">Heads</button>
        <button class="btn btn-primary">Tails</button>
        <button class="btn btn-primary">Abort</button>
        Outcome: <span promise-observer></span>
    </div>
</body>
</html>
```

<p data-height="265" data-theme-id="0" data-slug-hash="BdrVQv" data-default-tab="result" data-user="xmoyking" data-embed-version="2" data-pen-title="promises" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/BdrVQv/">promises</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>

promiseWorker指令依赖$q服务，在工厂函数中调用$q.defer方法获取新的deferred对象，然后在link函数和控制器中能够使用它。
link函数使用jqLite定位button元素并绑定click事件，在收到事件中，检查被单击元素的文本并调用deferred对象的resolve方法（为Heads和Tails按钮）或者reject方法（Abort按钮）两者之一，控制器定义promise属性来映射deferred对象和promise属性，通过控制器暴露该属性，可以允许其他指令获取与deferred对象有关的promise对象，并接收关于结果的信号。

defered对象用于标识用户单击按钮的结果，然后创建一个新的指令promiseObserver，监控和更新span元素的内容。
promiseObserver指令使用require定义属性从其他指令中取得控制器，并获取promise对象，该对象定义如下方法：
- then(success, error, notify) 注册被函数以响应deferred对象的resolve，reject和notify方法，该函数所传参数是用于调用deferred对象的方法
- catch(error) 仅注册错误处理函数
- finally(fn) 注册无论解决还是拒绝都会被调用的函数，

#### 理解promises
感觉好像promises并没有什么出彩的地方，甚至还不如ajax理解起来那么容易，在上例中，promises表示一个活动的单一实例，一旦被解决或拒绝，promises无法再次使用，比如，单击Heads按钮，结果显示Heads，然后单击Tails按钮无效，因为promises已经被解决，无法再次使用，一旦设置，结果不变。
这意味这给观察者信号是“第一次用户选择Heads/Tails/Aborts”，如使用常规js的click事件，那其中每个仅能反映“用户单击按钮”，而不管用户单击的顺序和方式，即click事件可以重复，promises不能重复，一旦确定，即发出单一的活动结果作为信号。

链接多个then函数解决多个promises顺序使用
```js
angular
  .module("exampleApp", [])
  .directive("promiseWorker", function($q) {
    var deferred = $q.defer();
    return {
      link: function(scope, element, attrs) {
        element.find("button").on("click", function(event) {
          var buttonText = event.target.innerText;
          if (buttonText == "Abort") {
            deferred.reject("Aborted");
          } else {
            deferred.resolve(buttonText);
          }
        });
      },
      controller: function($scope, $element, $attrs) {
        this.promise = deferred.promise;
      }
    };
  })
  .directive("promiseObserver", function() {
    return {
      require: "^promiseWorker",
      link: function(scope, element, attrs, ctrl) {
        ctrl.promise
          .then(function(result) {
            return "Success (" + result + ")";
          })
          .then(function(result) {
            element.text(result);
          });
      }
    };
  })
  .controller("defaultCtrl", function($scope) {});
```
<p data-height="265" data-theme-id="0" data-slug-hash="YxajPB" data-default-tab="result" data-user="xmoyking" data-embed-version="2" data-pen-title="promises链" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/YxajPB/">promises链</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>


通过$q.all解决多个promises协同使用
```html
<html ng-app="exampleApp">
<head>
    <title>Promises</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", [])
        .directive("promiseWorker", function ($q) {
            var deferred = [$q.defer(), $q.defer()];
            var promises = [deferred[0].promise, deferred[1].promise];
            return {
                link: function (scope, element, attrs) {
                    element.find("button").on("click", function (event) {
                        var buttonText = event.target.innerText;
                        var buttonGroup = event.target.getAttribute("data-group");
                        if (buttonText == "Abort") {
                            deferred[buttonGroup].reject("Aborted");
                        } else {
                            deferred[buttonGroup].resolve(buttonText);
                        }
                    });
                },
                controller: function ($scope, $element, $attrs) {
                    this.promise = $q.all(promises).then(function (results) {
                        return results.join();
                    });
                }
            }
        })
        .directive("promiseObserver", function () {
            return {
                require: "^promiseWorker",
                link: function (scope, element, attrs, ctrl) {
                    ctrl.promise.then(function (result) {
                        element.text(result);
                    }, function (reason) {
                        element.text(reason);
                    });
                }
            }
        })
        .controller("defaultCtrl", function ($scope) {

        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="well" promise-worker>
        <div class="btn-group">
            <button class="btn btn-primary" data-group="0">Heads</button>
            <button class="btn btn-primary" data-group="0">Tails</button>
            <button class="btn btn-primary" data-group="0">Abort</button>
        </div>
        <div class="btn-group">
            <button class="btn btn-primary" data-group="1">Yes</button>
            <button class="btn btn-primary" data-group="1">No</button>
            <button class="btn btn-primary" data-group="1">Abort</button>
        </div>
        Outcome: <span promise-observer></span>
    </div>
</body>
</html>
```

<p data-height="265" data-theme-id="0" data-slug-hash="yoKqYM" data-default-tab="result" data-user="xmoyking" data-embed-version="2" data-pen-title="多个promises协同" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/yoKqYM/">多个promises协同</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>