---
title: AngularJS巩固实践-46-编码规范-1
categories:
  - AngularJS
tags:
  - AngularJS
  - ng编码规范
date: 2017-09-03 16:42:05
updated:
---

两个术语：“编码规范”、“编程风格”的区别：编程风格是编码规范的一种，用来规约单个文件中的代码规范，编码规范还包含编程的最佳实践、文件和目录组织规范等诸多方面。

软件工程化“黄金定律”：一个项目应该永远遵循一套编码规范，不管多少人共同参与同一项目，一定要确保每一行代码都像同一个人编写的。

编码规范统一非常重要，原因如下：
1. 每一个开发者不用去寻找写代码者是谁，也不需要额外花费精力去理解代码逻辑并依据自己的编码习惯重新改编，每个人都在同一上下文工作，能极大节约时间成本。
2. 能够很容易识别代码的问题并发现问题，当出现一段与众不同的代码时，很有可能问题就出自此。
3. 将一些具体技术、框架的最佳实践、常见的问题和坑编入项目编码规范，能减少很多爬坑时间，并在一定程度上降低代码中潜在的bug。比如：parseInt函数始终传入第二个参数，默认为10，避免错误的转换为其他机制。

### 目录结构
目录结构对一个项目是非常重要的，一个好的目录结构能快速找到需要修改的文件，同时帮助成员高效工作并协调一致。ng项目主要分为两类目录结构组织方式：类型优先型和业务优先型。

#### 类型优先型
类型优先型是ng团队最初的angular-seed的组织方式。

- app
  - app.js
  - controller
    - FirstCtrl.js
    - SecondCtrl.js
    - ThirdCtrl.js
  - directives
    - directive1.js
    - directive2.js
    - directive3.js
  - filters
    - filter1.js
    - filter2.js
    - filter3.js
  - services
    - service1.js
    - service2.js
- lib
- test

按照ng的组件类型，将Controller、Directive、Service、Filter分别放到不同的目录下，并用一个module来组织。它能快速定位文件位置，但不容易将相关代码凑到一起复用到其他项目，所以更适合小型项目。

#### 业务优先型
按照业务Feature分类（Feature是一个业务领域的概念，它可能对应一个页面或好几个业务，比如订单、商品、支付都是独立的Feature模块），将不同的Feature分配在不同的目录结构下，他们都拥有自己独立的module，在module同级有按类型组织的Controller、Directive、Service、Filter目录结构，最后再将所有的Feature注入到应用程序的全局module中，对于模块之间公用的组件，将放入common的目录下，它们可以每个组件构建单独的module，也可以放在统一的module之下。

- app
  - mainModule.js
  - common
    - controllers.js
    - directives.js
    - filters.js
    - services.js
  - feature1
    - feature1Module.js
    - controllers
      - FirstCtrl.js
      - SecondCtrl.js
    - directives
      - directive1.js
    - filters
      - filter1.js
      - filter2.js
    - services
      - service1.js
      - service2.js
  - feature2
    - feature2Module.js
    - controllers
      - FirstCtrl.js
      - SecondCtrl.js
    - directives
      - directive2.js
    - filters
      - filter3.js
    - services
      - service3.js
- lib
- test

这样就解决了类型优先型目录结构业务分离的问题，团队保持一致上下文，也能快速定位文件，高效协作。

若针对组件开发，可以尝试将组件的HTML、CSS、JS放在同一位置，以便统一打包发布到bower或npm服务器，在多个项目之间共享，对于HTML文件可以尝试用ng-html2js打包到JS中，缓存在$templateCache服务中。

- app
  - directives
    - directive1
      - directive1.html
      - directive1.js
      - directive1.sass
    - directive2
      - directive2.html
      - directive2.js
      - directive2.sass

### 模块组织
#### 命名
ng module是ng中js代码项目的组织形式，以及模块之间的依赖方式，按照功能划分，将Controller，Service，Filter，Directive内聚在一起，作为可复用的组件模块，建议如java那样，以全小写和`.`分割保证唯一命名。
```js
angular.module('com.ngnice.app', ['dependency1','dependency2', ...])
```

#### Module声明
对于同一个业务模块，只在一个文件中用双参数重载方法创建module：`angular.module('com.ngnice.app',[])`,其他文件使用单参重载方法获取module：`angular.module('com.ngnice.app')`。不要添加额外的js变量来保存module。
```js
// controller 声明
angular.module('com.ngnice.app').controller('DemoController', function(){
  // ...
});

// service 声明
angular.module('com.ngnice.app').service('demoService', function(){
  // ...
});
```

#### 依赖声明
在module声明的时候，应该随时保证模块没有无用的依赖或是冗余的依赖，模块的依赖具有传递性，对于子模块的依赖，同样也会被其所注入的主模块所依赖。应该保证每个模块都自己声明了它所依赖的模块，禁止在主模块中声明某依赖不使用而在其子module使用。这样做的问题是，让本该独立使用的模块却不能单独复用到其他模块了。

#### Module组件声明
将module中的Controller、Service、Factory、Filter、Directive等移到独立文件，并保证组件名称和文件名同步，这样便于复用，也便于快速查找相应的业务组件。对于config、run以及路由这类公共初始化代码可以放在module声明文件，也可以独立出去。把路由按照功能分到多个子模块，而不是只有一个全局路由配置文件:
```js
// bookModule.js
angular.module('com.ngnic.app').config(function($routeProvider){
  $routeProvider
    .when('/book', {
      templateUrl: 'bool.html',
      // ...
    })
    .when('/book/:id',{
        templateUrl: 'bool.html',
        // ...
    });
});

// DemoController.js
angular.module('com.ngnice.app').controller('DemoController', function(){
  var vm = this;
  return vm;
});

// demoService.js
angular.module('com.ngnice.app').service('demoService', function(){
  var self = this;
});
```

### 控制器
#### 命名
Controller提供了对$scope的初始化和加工处理，并不需要手动实例化。ng会更具路由或ng-controller配置，利用$controller服务自动实例化它。建议以首字母大写的驼峰命名，以及加“Controller”后缀方式命名。

#### Controller as vm 声明
ng1.2后引入Controller as语法，它是$scope方式的语法糖，使得Controller声明更像一个普通的js构造函数POJO，在View模版上ng-controller、路由配置和Directive声明上都可以使用controller as语法。vm是ViewModel的简称，它将ngModel变量中强制加入`.`变为引用对象，避免js在对原型链上值类型修改的问题。
```js
// DemoController.js
angular.module('com.ngnice.app').controller('DemoController', function(){
  var vm = this;

  return vm;
});

// 视图
<div ng-controller="DemoController as demo"></div>

// 路由
angular.module('com.ngnice.app').config(function($routeProvider){
  $routeProvider.when('/Book/:bookId', {
    templateUrl: 'demo.html',
    controller: DemoController,
    controllerAs: 'demo'
  });
});
```

#### 初始化数据
将页面中的ng-init初始化数据方式，移到Controller代码中，对于默认配置甚至应该推到服务中去，不要让View模版过于复杂，并对于$scope的初始化统一放在Controller代码中，这样便于维护、分工。
```js
// 不好的用法
<div ng-init='person={name:"张三"}'>
  <pre>{{person | json}}</pre>
</div>

// 推荐用法
<div>
  <pre>{{demo.person | json}}</pre>
</div>

angular.module('com.ngnice.app').controller('DemoController', function(){
  var vm = this;
  vm.person = {
    name: '张三'
  }
})
```

#### DOM操作
禁止在控制器Controller中操作DOM，将必须的DOM操作移动并封装成一个独立的装饰型指令，在它的link方法中完成，并保持这个指令的功能尽可能简单。

#### 依赖的声明
坚持以数组语法方式定义控制器和声明其依赖，对于ng自动解析参数形式的注入，会在js函数混淆的时候，参数名被简化改变，导致注入失败，程序出错。所以坚持以数组的方式或function.$inject方式注入是好的实践，由于使用function.$inject方式注入时需要声明特定的函数名，这样对只使用一次的js函数来说，显得冗余，所以推荐数组方式声明依赖。若使用ES6或CoffeeScript的class方式声明，更推荐function.$inject方式。
```js
angular.module('com.ngnice.app').controller('DemoController', ['demoService', function(demoService){
  // ...
}]);
```

对于已经存在自动按参数名注入的项目，可以采用ngAnnotate或者ngMin插件，他们都有Gulp和Grunt插件。

#### 精简控制器逻辑
Controller作为加工处理$scope的地方，应该尽可能的精简控制器的逻辑，并将更多的业务逻辑处理抽象到独立的服务中去，这样即便于维护，也能够通过服务来复用到更多的Controller中。

$scope上，应该仅仅添加与视图相关的行为和数据，这些数据都会被View直接使用，不要将无用的数据或函数添加到$scope上，污染$scope，及时清理无用的$scope变量。

#### 禁止用$rootScope传递数据
$rootScope是最顶级的scope，其他的scope都是直接或间接派上于它的，在这里声明的变量会被所有非独立scope共享，本质上，它就是一个全局变量，所以应该禁止利用$rootScope来传递和共享数据。

若确实需要全局变量，应该根据场景选择不用的解决方案：
- 若两个控制器之间耦合比较松散（比如：控制器A发生变化，它会告诉其他控制器，但并不关心谁会处理，也不关心处理结果），那么应该选用事件机制：$emit, $broadcast, $on等，若要通知下级，就用$broadcase,通知上级则用$emit，通知整个应用，则注入$rootScope，然后调用$rootScope.$boradcast向整个应用广播，ui-router等路优酷就是这种方式
- 若两个控制器存在大量数据共享和交互，那么有2中可选方案，可以利用Factory等服务单例特性为他们专门注入一个共享对象来传递数据，也可以通过它们共同的上级scope来传递数据（但这种方式只能传递被所有下级scope使用的数据），根据软件设计的“组合优于继承”原则，前面一种更好。

#### 格式化显示逻辑
对于需要将原始数据转换为特定的用户格式，如货币，时间，过滤，数字格式化等，应该将这部分逻辑抽取成一个Filter，而不要写在Controller中，Filter是专门处理View中格式转化的代码块，它是一个更简单、纯粹的js函数，并且会在每次View被渲染时自动执行。

#### Resolve
路由库，如ui-router或ngRoute提供了resolve机制，用来在进入特定路由之前进行预处理。

若在实例化控制器之前，需要准备一些特定数据，或有条件的阻止禁止路由，那么可以在$routeProvider中配置Resolve属性来解决，Resolve是一个对象，它的key是名称，值可以是一个Promise的异步请求，也可以是一个服务的字符串值。

此外，若只是要阻止进入特定的路由，可以使用ngRoute的$locationChangeStart或uiRoute的$stateChangeStart事件。

