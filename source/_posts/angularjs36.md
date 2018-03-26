---
title: AngularJS巩固实践-36-$parse和$eval、$observe和$watch
categories:
  - AngularJS
tags:
  - AngularJS
date: 2017-08-12 10:40:09
updated:
---

在ng源码中，很多地方会用到$parse和$eval、$observe和$watch,这2对指令非常有用和重要，应该清楚他们的用途和区别。

#### $parse和$eval
$parse和$eval这两个函数可以解析/计算ng表达式的值。

区别在：
- $parse是独立服务，可以在任意地方注入后使用，它返回一个函数，然后需要显式的把表达式求职的上下文传给这个函数。
- $eval则是scope对象上的一个方法，它是对$parse的包装，默认已指定表达式的求值上下文为所在的scope对象，所以传入参数后返回计算结果，也就是说，只能在能访问到scope的场景下使用$eval，如Controller中或指令的link函数中。

$eval源码：
```js
$eval: function(expr, locals){
  return $parse(expre)(this, locals);
}
```
即：$eval是为了让$parse在scope中更方便使用的语法糖，$parse和$eval支持指定上下文，即locals参数。

使用$parse时，需先传入表达式进行解析，然后返回一个解析后的函数，该函数本身时用来获取对象的值的。对属性表达式解析时，$parse还会生成一个assign的属性，代表相应的赋值函数：
```js
var getter = $parse('user.name'); // 解析表达式
var setter = getter.assgin; // 获取赋值函数
var context = {user: {name: 'ng'}};
var locals = {user: {name: 'local'}};

expect(getter(context)).toEqual('ng'); // true
setter(context, 'new value'); // 设置属性值
expect(context.user.name).toEqual('new value'); // true
expect(getter(context, locals)).toEqual('local'); // true
```
上例中，$parse先解析表达式user.name, 若时属性表达式则返回对应的getter函数，同时设置assign属性为对应的setter函数，最后演示如何利用locals来指定上下文。

由于不需要指定上下对象，$eval的使用就简单多了，因为其已经被强制指定为$scope对象为上下文：
```js
var scope = $rootScope.$new(true); // 新建一个scope
scope.a = 1;
scope.b = 2;

expect(scope.$eval('a+b')).toEqual(3); // true
expect(scope.$eval(function(scope){
  return scope.a + scope.b;
})).toEqual(3); // true
```

$eval还有一个异步版本，$evalAsync,它会将表达式缓存起来，等到下一次的$digest开始前执行，这样能获得较好的性能。

#### $observe和$watch
$observe和$watch都可用于监听值的变化，但$observe是用来监听DOM中属性值变化的，而$watch则是监听scope中属性值的变化的。

一般情况下$watch已经足够，但当在指令的DOM属性上使用了ng表达式时（即`{ { } }`）,这时则DOM的属性值为字符串而不是表达式的运算结果。

$observe源码（定义在compile.js中）：
```js
$observe: function(key, fn){
  var attrs = this,
      $$observers = (attrs.$$observers || (attrs.$$observers = {})),
      listeners = ($$observers[key] || ($$observers[key] = []));

    listeners.push(fn);
    $rootScope.$evalAsync(function(){
      if(!listeners.$$inter){
        fn(attrs[key]);
      }
    });

    return fn;
}
```
由上可知，$observe是通过$evalAsync函数实现，它会延迟到下一轮脏检查时执行，由于$observe方法是定义在link函数的第三个参数iAttrs上的，所以只能在指令的link函数中使用它。

$observe的回调函数只有一个参数，那就是新值，而$watch有两个参数，分别是新值和旧值

比如，指令在DOM中属性如下：
```html
<div book="Name:{{book.name}}"></div>
```
则在指令中的使用为：
```js
iAttrs.$observe('book', function(newValue){
  ...
});
```
上面代码若改为`scope.$watch(iAttrs.book, ...)`是无效的，因为book属性值不能被$eval解析。

相对$observe, $watch则复杂但也灵活一些，它可以监听一个函数或一个字符串，若是字符串则自动封装为一个简单函数，然后在每次$digest循环时被调用，但这个表达式字符串不能包含{ {} }，因为它实际上是一段js代码，会被$eval执行。

如下DOM属性中定义的表达式不包含{ {} }：
```html
<div book="book.name"></div>
```
若$watch：
```js
scope.$watch('book.name', function(newVaule, oldValue){
  ...
});
```
或在link函数中:
```js
// iAttrs.book 的值为 'book.name'
scope.$watch(iAttrs.book, function(newValue, oldValue){
  ...
});
// 若换成iAttrs.$observe('book')的话，则只被调用一次，值为“book.name”
```

#### 使用场景
关于独立scope声明中的“@”，“&”,"="三种形式，一直没明白到底是如何使用的，可以先看下这个帖子，学习如何使用[AngularJS Directive 隔离 Scope 数据交互](https://blog.coding.net/blog/AngularJS-directive-isolate-scope?type=early)

先看最简单的“&”的实现，它将属性值传给$parse服务，在父scope上解析未一个可调用的计算函数，再包装成一个只需要locals参数的函数，然后存放在子scope上。它定义再父scope上，但可在子scope中被调用，这样就实现了子scope对父scope的回调。需要注意：参数中可以传入一个本地的上下文对象，用于覆盖或新增变量（原来上下文对象中不存在的）。
所以，若在DOM中定义：
```html
<div buy-book="buyBook($bookId, $amount);"></div>
```
在子scope中可用如下方式调用：
```js
scope.buyBook({
  $bookId: 1111,
  $amout: 2
});
```
注意：传递的参数是本地上下文对象，所以应该是一个Object对象，以参数名为key值，参数值为value。

然后是"@"的实现，它使用attrs.$observe来监听DOM属性的变化，当属性值发生变化后，它直接将新值放在指令的独立scope上。由于attrs.$observe的值总是字符串，所以“@”值也一样，若DOM中表达式{ {} }计算得到新值，它就会触发，然后ng还会检查若存在表达式顶会议，则解析并未scope赋初始值。

最后是"="的实现，用到了scope.$watch函数，而在$watch函数中，ng会先比较父scope和子scope之间是否有变化，若有则同步两者。然后再将缓存的原值和父scope的当前值比较，判断父scope发生了变化还是子scope发生了变化。总之，要将两者同步。由于$watch函数监听的是scope上的属性，所以再使用“=”定义时，不能包含{ {} }表达式，而应该是一个能被$eval解析的合法表达式。

下面为ng实现源码：
```js
forEach(newIsolateScopeDirective.scope, function(definition, scopeName){
  ...
  isolateScope.$$isolateBindings[scopeName] = mode + attrName;

  switch(mode){
    case '@':
      attrs.$observe(attrName, function(value){ // 监听Attribute值的变化
        isolateScope[scopeName] = value;
      });
      attrs.$$observers[attrName].$$scope = scope; // 赋值为了在指令中的监听使用
      if(attrs[attrsName]){
        isolateScope[scopeName] = $interpolate(attrs[attrName])(scope); // 解析初始值，赋值在scope上
      }

      break;

    case '=':
      if(optional && !attrs[attrName]){ // Attribute必须有值，因为它需要指向父scope的model属性
        return;
      }
      parentGet = $parse(attrs[attrName]);
      if(parentGet.literal){ // 获取对属性值的比较函数
        compare = equals;
      }else{
        compare = function(a,b){ return a === b || (a !== a && b !== b); };
      }
      parentSet = parentGet.assign || function(){
        ... // 抛异常
      };

      lastValue = isolateScope[scopeName] = parentGet(scope); // 缓存初始值，用于区分父还是子scope的变化
      isolateScope.$watch(function parentValueWatch(){ // 监听父scope的属性是否发生变化
        var parentValue = parentGet(scope); // 获取父scope的值
        if(!compare(parentValue, isolateScope[scopeName])){ // 比较父scope和子scope是否变化
          if(!compare(parentValue, lastValue)){ // 若父scope不等于旧值，那么说明父变化，将赋值给子scope
            isolateS cope[scopeName] = parentValue;
          }else{ // 若父未变化，则子变化，将子scope变化同步到父scope
            parentSet(scope, parentValue = isolateScope[scopeName]);
          }
        }
        return lastValue = parentValue; // 缓存本次的值，便于下次比较
      }, null, parentGet.literal);

      break;

    case '&':
      parentGet = $parse(attrs[attrName]); // 将Attribute的值解析未ng解析表达式函数
      isolateScope[scopeName] = function(locals){ // 将Attribute中的解析函数继续封装未一个函数，供子scope调用
        return parentGet(scope, locals); // 调用父scope的声明方法
      }：
      break;

    default:
      ... // 抛异常
  }
});
```