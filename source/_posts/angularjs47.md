---
title: AngularJS巩固实践-47-编码规范-2
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - ng编码规范
  - AngularJS深度剖析
date: 2017-09-04 15:57:38
updated:
---

接上文[AngularJS巩固实践-46-编码规范-1](/2017/09/03/AngularJS46/)

### 服务
#### 命名
服务包括Service、Factory、Value、Constant、Provider等，他们是ng中提供复用逻辑封装的组件，在使用这些服务的地方，需要利用根据所注入服务的用途决定其命名格式：
- 若是作为实例使用，建议“小写首字母驼峰命名（小驼峰）”
- 若是作为类使用，建议“大写首字母驼峰命名（大驼峰）”
```js
// 当前用户，作为实例使用
angular.module('com.ngnice.app').service('currentUser', function(){
  this.name = 'abc';
  // ...
});

// 用户REST资源，作为类使用
angular.module('com.ngnice.app').service('Users', function($resource){
  return $resource('/api/users/:id', {id: '@id'});
});
```

#### 代码复用
Service是复用逻辑代码的组件，所以应该将控制器、指令中的业务逻辑封装到Service中。但不能将$scope这类参数传递到Service中，可将需要传入的对象封装为参数对象，且应该只传递所需要的数据。

Service具有单例的特性，因此对于全局View组件，如Modal对话框等，可以尝试封装为Directive，然后在Service中用$compile编译他们。可以参考angular-ui中Modal服务源码。

#### 使用场景
ng中提供了Service、Factory、Value、Constant、Provider等声明方式，它们各自针对不同的使用场景：
- 对于项目配置信息，可以选择Constant，它们可以在ng的config阶段注入，Constant虽然是常量不可变，但对于对象地址的引用，内部属性是可以被修改的，所以推荐使用引用类型。
- 对于已经存在实例对象的服务，Factory优先，直接返回这个对象，如：在多个Controller之间传递共享数据；对$resource的请求资源的封装。
- 对于需要new创建的服务而言，则Service优先，ng会自动new并创建这个对象实例。Service更容易组织一组相同业务逻辑的API，使得业务逻辑更加内聚。
- 对于需要在实例化之前进行特定配置的服务，则使用Provider声明，在Provider服务中定义的服务配置函数，可以在Config阶段被使用。

#### Service返回值
对于服务中函数的返回值，尽量保持统一，都返回Promise对象，使用时统一通过`then(success, error)`或`catch(error)`方式。

对于同步的函数，例如webstorage、cookie等操作，建议通过`$q.when()`将其封装为Promise形式，这样就可以实现如ng的拦截器interceptor一样的管道式AOP机制。

#### 缓存不变数据
对于通过$http或者$resource等方式从后端获取的数据，若在SPA的生命周期内不会发生变化，就应该将其缓存起来，减少服务器的负荷，提高性能。
```js
$http.post('/url',{
  id: 1,
  name: 'king',
}, function(res){
  // ...
  catch: true
});
```

#### RESTful
对于RESTful项目优先使用$resource， $resource是在$http基础上为RESTful API专门封装的服务，对资源的CRUD操作提供了统一调用接口。

### 过滤器
#### 命名
对于每一个Filter，ng背后都会将其转换为一个名为xxxFilter的服务，本质上是一个服务的实例对象，所以应该使用“小驼峰”格式声明自定义Filter。

#### 重用已有Filter
若在其他非View的地方，如Controller、Service等需要复用已有Filter的逻辑，那么可以使用xxxFilter的方式注入该Filter服务以重用它的逻辑：
```js
angular.module('com.ngnice.app').filter('fullName', function(){
  return function(input, param, /* ... */ ){
    // code
  };
});

angular.module('com.ngnice.app').controller('DemoController', function(fullNameFilter){
  var vm = this;
  var input = 'ipt';
  var param = 'prms';
  fullNameFilter(input, param);

  return vm;
});
```

#### 禁止复杂的Filter
Filter在每次View渲染的时候，都会在重新执行，所以应该尽可能的简化过滤器的逻辑，不要在Filter中写大量底效率的复杂逻辑，否则会拖慢整个应用。

过滤器是一个JS函数，与函数式对集合的处理流程相似，推荐引入ES6函数式处理，再通过Babal之类的工具将其转换为ES5代码发布。

### 指令
#### 命名
由于HTML不区分大小写，所以ng规范规定Directive以驼峰格式声明，它将会转换为全小写`-`分割的元素名或属性名，如：patmentInfo将转换为paymeny-info

对可复用指令，应该在命名时加上一个短小、唯一、具有描述性的前缀，例如xxTitle。好的前缀能够方便快速识别Directive的内容和起源、防止与其他第三方库出现命名冲突。同时需要避免与已有的常用开源库前缀相同。

#### Template声明
指令中的模版，可以用template属性， 也可以用templateUrl属性，相对而言，templateUrl优于template，它可以指向独立的HTML页面URL（也可以是ng-html2js的$templateCache的缓存简直），或是ng-template的id，这样将HTML和JS分离有利于维护和开发。

#### link函数的scope参数命名
link函数（pre-link和post-link）中的scope对象是一个方法参数，而不是被注入的服务，为了体现这一点，应该以scope命名而不是$scope。

controller as语法、bindToController = true等将独立作用域的变量绑定到controllerAs对象中：
```js
angular.module('com.ngnice.app').controller('DemoController',function(){
  var vm = this;
  vm.title = 'hello controller as';
  return vm;
});

angular.module('com.ngnice.app').directive('hello',function(){
  return {
    restrict: 'EA',
    controllerAs: 'vm', //改为具体的xxVm更好
    template: '<div>{{vm.title}}</div>', // 复杂情况下应该使用templateUrl
    controller: 'DemoController',
    bindToController: true,
  };
});
```

#### pre-link和post-link
在Directive compile周期中link方法分为pre和post两种，优先使用post，当需要给子节点准备使用数据时才使用pre。

#### DOM操作
指令是操作DOM的推荐方式，不要将DOM操作放在Controller组件中，因为那样难于测试、定位和解决问题。若能使用css来设置样式，animation service或ngShow/ngHide等内置指令解决，应该优先内置指令。

#### Directive分类
按照功能划分，主要分为组件型指令和装饰型指令。

组件型指的是为了分离关注点和语义化，而拆分View形成的指令，主要包括HTML模版和Controller初始化业务逻辑。应该使用Controller，而不是link函数，restrict一般为EA/E，

装饰型指令则是用来建立DOM和Model之间的桥梁，它一般需要在link函数中对DOM操作，但它的逻辑应该精简。一般restrict属性为A。

#### 自动回收
当Directive被移除DOM后，应该考虑Directive的自动回收，释放Scope，通过监听$destroy事件来做Directive的清理工作：
```js
angular.module('com.ngnice.app').directive('directiveName', function(){
    return {
      restrict: 'EA',
      link: function(scope, elm, iAttrs){
        scope.$on('$destroy', function(){
          // ...
        });
      }
    };
});
```

### 模版
#### 表达式绑定
ng在浏览器解析表达式时，可能出现闪烁问题，为了实现更好的用户体验，应该使用ng-bind或抱在ng-cloak指令中的ng表达式来防止页面渲染时的闪烁。

#### src/href问题
当直接在src或href属性中插入`{ {} }`表达式时，浏览器会在表达式被解析之前，尝试一次错误的加载，这回出现一个多余的404错误，所以ng专门提供ng-src、ng-href指令来代替src、href属性，可以安全的嵌入表达式。

#### class优于style
对css来说，直接用style属性在html节点上是一种不好的方式，不便于维护和复用前端样式，所以应优先用class，ng中的ngClass和ngStyle也是如此，只有在需要动态计算位置等场景下，使用ngStyle才是合理的。
