---
title: AngularJS巩固实践-48-常见“坑”-1-module函数的声明和获取重载
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - AngularJS深度剖析
date: 2017-09-7 23:38:53
updated:
---

常见“坑”系列：记录一些需要特别注意的的地方与坑。

Module是ng中重要模块组织方式，将一组业务组件（Controller，Service，Filter，Directive...)内聚地封装在一起，将代码按照业务领域划分一个模块，然后在其他模块中声明对这个模块的依赖。这样能更好的“分离关注点”，实现“高内聚低耦合”，“高内聚低耦合”中内聚指的是模块或对象内部的完整性，一组紧密联系的逻辑应该封装在同一个代码单元（模块/对象），而不是分散，耦合指的是代码单元之间的依赖成都，如一个模块的修改会引起另一个模块随之修改则说明这两个模块之间是紧耦合。

同时Module也是ng的代码入口，只有在声明了Module的情况下，才能定义ng组件（Controller，Service，Filter，Directive，Config，Run等）

Module的定义为：`angular.module('app', [])`,module函数接受三个参数，分别为：
- name, 模块的名称，它应该是全局唯一的，同时也是必选参数，既可以被其他模块所依赖，也可以作为ngApp指令所引用的主模块。
- require，模块的依赖，它指当前模块所依赖的其他模块，需注意，若在此没有声明模块的依赖，则无法在当前模块中使用来自所依赖模块的任何组件。require参数是可选的，若没有传递该参数则表示获取module，反之则表示创建module。
- configFn，模块的启动配置函数，该函数会在ng的config阶段被调用，实现对Provider的全局配置，如$routeProvider的路由信息配置，该配置函数等同于`module.config`方式声明配置信息，一般用`module.config`方式声明。configFn参数是可选的。

推荐将ng组件放在独立文件内，用一个单独的module文件来创建module和声明module的依赖，其他文件则只获取module，同时在打包或script引入时，需要先加载创建module的文件，然后再加载其他注册ng组件的文件，在FrontJet中，集成了一个gulp-angular-filesort插件，它会自动完成排序工作，保证先加载创建文件。

有一个`ng:areq`的问题:`[ng:areq] Argument 'DemoController' is not a function, got undefined!`
出现的原因若不是忘记定义Controller，那就很有可能时多次创建module，在每次创建module时，都会导致之前创建的module定义信息被清空，以定义的ng组件也会丢失，以下时ng源码：
```js
function setupModuleLoader(window){
  // ...
  function ensure(obj, name, factory){
    return obj[name] || (obj[name]) = factory());
  }
  var angular = ensure(window, 'angular', Object); // 开放window.angular的对外接口

  return ensure(window, 'module', function(){
    var modules = {};
    return function module(name, require, configFn){
      var assertNotHasOwnProperty = function(name, context){
        if(name === 'hasOwnProperty') {
          // module 名称不能声明为hasOwnProperty
          throw ngMinErr('badname', 'hasOwnProperty is not a valid {0} name', context);
        }
      };

      assertNotHasOwnProperty(name, 'module');
      if(require && modules.hasOwnProperty(name)) {
         //存在requires则为module声明， modules.hasOwnProperty(name) 为true，则说明，已经声明过此模块
         module[name] = null;
      }

      return ensure(modules, name, function(){
        if(!requires){
          // 在使用前，必须声明module，
          throw $injectorMinErr('nomod', "Module '{0}' is not avaliable! You either misspelled " +
            "the module name or forgot to load it. If registering a module ensure that you " +
            "speciafy the dependencies as the second argument.", name
          );
        }

        var invokeQueue = [];
        var runBlocks = [];
        var config = invokeLater('$injector', 'invoke');
        var moduleInstance = {
          // 各API声明
          _invokeQueue: invokeQueue,
          _runBlocks: runBlocks,
          requires: requires,
          name: name,
          provider: invokeLater('$provide', 'provider'),
          factory: invokeLater('$provide', 'factory'),
          service: invokeLater('$provide', 'service'),
          value: invokeLater('$provide', 'value'),
          constant: invokeLater('$animateProvider', 'register'),
          filter: invokeLater('$filterProvider', 'register'),
          controller: invokeLater('$controllerProvider', 'register'),
          directive: invokeLater('$directiveProvider', 'register'),
          config: config,
          run: function(block){
            runBlocks.push(block);
            return this;
          }
        };

        if(configFn){
          config(configFn); // 缓存模块配置函数
        }
        return moduleInstance;

        function invokeLater(provider, method, insertMethod){
          return function(){
            invokeQueue[insertMethod || 'push']([provider, method. arguments]);
            return moduleInstance; // 返回模块实例，形成链式访问
          };
        }
      });
    };
  });
}
```

首先ng会先确保全局的windo.angular 可用，然后在ng对象上暴露module方法，若名称时hasOwnProperty则会引起混淆，所以需要抛出错误。

在module函数重载中，若requires参数的存在，则表示module为创建，同时若已经存在同名module，则会自动清空已存在的module信息，将其置为null。

在angular.module的返回值moduleInstance中，暴露了ng组件的API，其中_invokeQueue 和 _runBlocks是按名约定的私有属性，不建议使用，其余API都是ng常用的组件声明API，所有的ng组件的定义都会通过invokeLater函数代理，并且它返回值一直保持为moduleInstance实例，方便链式调用。所以推荐使用链式调用而不是声明在一个全局的module变量上。

最后，若传入第三个configFn函数，则它被配置到config信息上，在ng进入config阶段，所有的config信息将会被依次执行，实现对应用或ng组件对象实例的特定配置。
