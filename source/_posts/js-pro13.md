---
title: JavaScript高级程序设计-13-函数表达式1-递归/闭包
categories: js
tags:
  - js
  - js-pro
date: 2016-08-12 13:52:47
updated:
---

函数表达式是js中一个强大但容易令人困惑的特性。定义函数有两种方式，分别为函数声明和函数表达式。

通过函数声明创建函数的语法为：
```js
function functionName(arg0,arg1,arg2,...){
  // 函数体
}
```
在许多浏览器实现中，函数有一个非标准的name属性，通过这个属性可以访问到给函数指定的名字，这个值与function关键字后的标识符相等。
```js
functionName.name; // 'functionName'
```
关于函数声明，需要注意的特点是**函数声明提升（function declaration hoisting）**，在执行代码之前会读取函数声明。所以可以将函数声明放在调用语句之后。

通过函数表达式创建函数的语法有好几种形式，最常见的是：
```js
var functionName = function(arg0,arg1,arg2,...){
  // 函数体
}
```
这种形式看起来与常规的变量赋值语句很想，即创建一个函数并将它赋值给变量functionName。因为function关键字后面没有标识符，在这种情况下创建的函数叫**匿名函数（anonymous function）**，有的时候也被称为lamda表达式，匿名函数的name属性是空字符串。同时，函数表达式与其他表达式一样，使用前需先赋值。

理解函数提升的关键在于理解函数声明与函数表达式之间的区别。例如下例的结果就始终为第二个定义：
```js
// 始终返回第二个声明
if(condition){
  function sayHi(){
    console.log('Hi');
  }
}else{
  function sayHi(){
    console.log('Hello');
  }
}
```
之所以浏览器会始终返回第二个声明是因为浏览器忽略condition条件判断。在ES中这样的写法是无效的。

ES中能先创建函数再赋值给变量，即将函数作为其他函数的返回。比如sort的第三个参数需要一个函数对象。
```js
function createComparisonFunction(propertyName){
  return function(object1,object2){
    var value1 = object1[propertyName];
    var value2 = object2[propertyName];

    if(value1 < value2){
      return -1;
    }else if(value1 > value2){
      return 1;
    }else{
      return 0
    }
  }
}
```
在createComparisonFunction内部返回一个匿名函数，返回的函数可能会被赋值给一个变量，也可以以其他方式调用。

### 递归
递归函数是在一个函数通过名字调用自身的情况下构成的：
```js
function factorial(n){
  if(n <= 1){
    return 1;
  }else{
    return n*factorial(n-1);
  }
}
```
但当使用如下方式调用时会报错：
```js
var f2 = factorial;
factorial = null;
console.log(f2(4)); // error
```
由于factorial变量被设置为了null，虽然原函数被赋值给了一个新变量，但却会导致原函数内部的factorial执行错误。

这种情况下使用arguments.callee可以解决问题，arguments.callee是一个指向正在执行的函数的指针，但在严格模式下不能通过脚本访问arguments.callee。

也可以通过命名函数表达式达到相同的结果：
```js
var factorial = (function f(n){
  if(n <= 1){
    return 1;
  }else{
    return n*f(n-1);
  }
})
```
将命名函数表达式f赋值给变量factorial。即便把factorial赋值给了另外的变量，函数名f仍然有效。

### 闭包
有时候经常会混淆**匿名函数**和**闭包**这两个概念。

闭包是指有权访问另一个函数作用域中的变量的函数，创建闭包的常见方式就是在一个函数内部创建另一个函数，以createComparisonFunction为例，
```js
function createComparisonFunction(propertyName){
  return function(object1,object2){
    var value1 = object1[propertyName]; // 注意
    var value2 = object2[propertyName]; // 注意

    if(value1 < value2){
      return -1;
    }else if(value1 > value2){
      return 1;
    }else{
      return 0
    }
  }
}
```
上述代码中，加注释的代码是createComparisonFunction函数中内部的一个匿名函数中的代码，这两行代码访问了外部函数中的变量propertyName。需要注意的是，当内部函数被返回且在其他被调用时，即使这个内部函数被返回了，它仍然可以访问变量propertyName。之所以还能访问这个变量是因为内部函数的作用域链中包含createComparisonFunction的作用域。

要彻底搞清楚闭包的细节，必须先理解函数第一次被调用时发生了什么，同时需要理解如何创建作用域链、以及作用域链有什么用。

当某个函数第一次被调用时，会创建一个执行环境（execution context）及相应的作用域链，并把作用域链赋值给一个特殊的内部属性（`[[Scope]]`）。然后，使用this、arguments和其他命名参数的值来初始化函数的活动对象（activation object）。但在作用域链中，外部函数的活动对象始终处于第二位，外部函数的外部函数的活动对象处于第三位,...直到作用域链终点的全局执行环境。

在函数的执行过程中，为读写变量值，需要在作用域链中查找变量,如下例：
```js
function compare(value1,value2){
  if(value1 < value2){
    return -1;
  }else if(value1 > value2){
    return 1;
  }else{
    return 0
  }
}
var result = compare(5, 10);
```
以上代码先定义了compare函数，然后在全局作用域下调用，当第一次调用compare时，会创建一个包含this、arguments、value1、value2的活动对象。全局执行环境的变量对象（包含this、result和compare）在compare执行环境的作用域链中则处于第二位，下图为compare函数执行时的作用域链：
![作用域链关系图](1.png)

后台的每个执行环境都有一个表示变量的对象——变量对象。全局环境的变量对象始终存在，而像compare函数这样的局部环境的变量对象，则只在函数执行的过程中存在。在创建compare函数时，会创建一个预先包含全局变量对象的作用域链，这个作用域链被保存在内部的`[[Scope]]`属性中，当调用compare函数时，会为函数创建一个执行环境，然后通过复制函数的`[[Scope]]`属性中的对象构建起执行环境的作用域链。为此，又有一个活动对象（在此作为变量对象使用）被创建并被推入执行环境作用域链的前端。

以compare为例的执行环境而言，其作用域链中包含两个变量对象:本地活动对象和全局变量对象，显然，作用域链本质上是一个指向变量对象的指针列表，它只引用但不实际包含变量对象。

无论何时在函数中访问一个变量时，会从作用域链中搜索具有相应名字的变量。一般来说，当函数执行完毕后，局部活动对象会被销毁，内存中仅保存全局作用域（全局执行环境的变量对象），但闭包的情况不同。

在另一个函数内部定义的函数会将包含函数（即外部函数）的活动对象添加到它的作用域链中，因此，在createComparisonFunction函数内部定义的作用域链中，实际上将会包含外部函数createComparisonFunction的活动对象。

以下列代码为例：
```js
var compare = createComparisonFunction('name');
var result = compare({name: 'Nicholas'}, {name: 'Greg'});
```
在匿名函数从createComparisonFunction中返回后，它的作用域链被初始化为包含createComparisonFunction函数的活动对象和全局变量对象，这样，匿名函数就可以访问在createComparisonFunction中定义的所有变量。同时，createComparisonFunction函数在执行完毕后，其活动对象也不会被销毁，因为匿名函数的作用域链仍然在引用这个活动对象，换而言之，当createComparisonFunction函数返回后，其执行环境的作用域链会被销毁，但它的活动对象仍然留在内存中，直到匿名函数被销毁后，createComparisonFunction的活动对象才会被销毁。

使用如下方式销毁匿名函数：
```js
var compareNames = createComparisonFunction('name'); // 创建函数
var result = compareNames({name: 'Nicholas'}, {name: 'Greg'}); // 调用函数
compareNames = null; // 解除对匿名函数的引用以便释放内存
```

下图展示了调用compareNames的过程中产生的作用域链之间的关系：
![调用compareNames的过程中产生的作用域链之间的关系](2.png)

由于闭包会携带包含它的函数的作用域，因此会比其他函数占用更多内存。虽然优化后的js引擎会尝试回收被闭包占用的内存，但还是要谨慎使用闭包。

#### 闭包与变量
作用域链的这种机制有一个副作用，那就是闭包只能取得包含函数的任何变量的最后一个值，因为闭包所保存的是整个变了对象，而不是某个特殊的变量，以下面代码为例：
```js
function createFunctions(){
  var result = new Array();
  for(var i = 0; i < 10; i++){
    result[i] = function(){
      return i;
    };
  }
  return result;
}
```
这个函数会返回一个函数数组，从表明看，似乎每个函数都返回了自己的索引值，但实际上，因为每个函数的作用域链中的保存着createFunctions函数的活动对象，所以它们引用的都是同一个变量i，当createFunctions函数返回后，i的值是10，此时每个函数都引用保存变量i的同一个变量对象，所以每个函数内部i的值都是10。

通过创建另一个匿名函数强制让闭包行为符合预期可以解决这个引用问题：
```js
function createFunctions(){
  var result = new Array();
  for(var i = 0; i < 10; i++){
    result[i] = function(num){
      return function(){
        return num;
      };
    }(i);
  }
  return result;
}
```
修改后的createFunctions函数中，没有直接把闭包赋值给数组，而是定义了一个匿名函数并将立即执行该匿名函数的结果赋值给数组，这里的匿名函数有一个参数num，即最终的函数要返回的值。在调用每个匿名函数时，传入变量i，由于函数参数是按值传递的，所以就会将变量i的当前值复制给参数num。而在匿名函数内部，又创建并返回了一个访问num的闭包，如此，result数组中的每个函数都有自己num变量的一个副本，这样就可以返回各自不同的数值了。

#### 关于this对象
在闭包中使用this对象可能会导致一些问题。this镀锡是在运行时基于函数的执行环境绑定的，在全局函数中，this等于window，而当函数作为某个对象的方法调用时，this等于那个对象。

一般情况下，匿名函数的执行环境具有全局性，因此this对象通常指向window（可通过call和apply改变函数执行环境）。有时由于编写闭包的方式不同，会有一些差异：
```js
var name = 'Window';
var obj = {
  name: 'object',
  getNameFunc: function(){
    return function(){
      return this.name;
    };
  }
}

obj.getNameFunc()(); // 非严格模式下为 'Window'
```
由于geNameFunc返回一个函数，因此调用obj.getNameFunc()()就会立即调用它返回的函数，但为什么匿名函数返回的却是全局name变量的值。

由于每个函数在被调用时，其活动对象都会自动取得两个特殊变量：this和arguments。内部函数在搜索这两个变量时，只会搜索到其活动对象为止，因此永远不可能直接访问外部函数中的这两个变量。不过，把外部作用域中的this对象保存在一个闭包能够访问到的变量里就可以让闭包访问该对象了。
```js
var name = 'Window';
var obj = {
  name: 'object',
  getNameFunc: function(){
    var that = this;
    return function(){
      return that.name;
    };
  }
}

obj.getNameFunc()(); // 'object'
```
修改后的getNameFunc函数中，将this赋值给了that变量，定义匿名函数中，在函数返回后，闭包仍然可以访问这个that变量，因此最后返回object。

注意arguments和this一样，也会有这样的问题。

有几种特殊的情况，this的值会被意外改变：
```js
var name = 'Window';
var obj = {
  name: 'object',
  getName: function(){
    return this.name;
  }
};
obj.getName(); // 'object'
(obj.getName)(); // 'object'
(obj.getName = obj.getName)(); // 'window'
```
上述代码中，第一个`obj.getName();`正常调用，此时this.name就是obj.name。

第二个`(obj.getName)();`在调用方法前先加上了括号，好像只是在引用一个函数，但this的值不变，因为obj.getName和(obj.getName)的定义是相同的。

第三个`(obj.getName = obj.getName)();`先执行了一条赋值语句，然后再调用赋值后的结果，因为赋值表达式的值是函数本身，所以this改变，结果为window。

#### 内存泄漏
由于IE9之前的版本对js对象和COM对象使用不同的垃圾回收策略，因此闭包在IE的这些版本会导致一些问题。具体来说就是若闭包的作用域中保存着一个HTML元素，那么就意味着该元素将无法销毁。
```js
function assignHandler(){
  var element = document.getElementById('someElement');
  element.onclick = function(){
    console.log(element.id);
  };
}
```
上述代码创建了一个作为element元素时间处理程序的闭包，而这个闭包则又创建了一个循环引用。由于匿名函数保存了一个对assignHandler的活动对象的引用，因此只要匿名函数存在，element的引用数至少为1，导致它占用的内存永远不会被回收。

解决方式如下：
```js
function assignHandler(){
  var element = document.getElementById('someElement');
  var id = element.id;

  element.onclick = function(){
    console.log(id);
  };

  element = null;
}
```
通过将element.id的一个副本保存在一个变量中，并且在闭包中引用该变量消除循环引用。但仅仅这样并不能解决内存泄漏问题，因为闭包会引用包含函数的整个活动对象，而其中包含着element，即使闭包不直接引用element，包含函数的活动对象中也仍然会保存一个引用。因此，有必要将element变量设置为null，这样就能解除对DOM对象的引用，顺利减少其引用数，确保正常释放其占用内存。
