---
title: JavaScript高级程序设计-14-函数表达式2-模仿块级作用域/私有变量
categories: js
tags:
  - js
  - js-pro
date: 2016-08-12 16:49:18
updated:
---

接上篇[JavaScript高级程序设计-13-函数表达式1-递归/闭包](/2016/08/12/js-pro13)

### 模仿块级作用域
js没有块级作用域的概念，这意味着快语句中定义的变量，实际上是在包含函数中而非语句中创建的：
```js
function outputNumbers(count){
  for(var i = 0; i < count; i++){
    console.log(i);
  }
  console.log(i); // 计数
}
```
在循环中定义的变量i初始化为0。在java，c++等语言中，变量i只会在for循环的语句快中有定义，循环一旦结束，变量i就被销毁。但在js中变量i是定义在outputNumbers的活动对象中的，因此从它有定义开始，就可以在函数内部随处访问。即使重新声明也不会改变，如下例：
```js
function outputNumbers(count){
  for(var i = 0; i < count; i++){
    console.log(i);
  }
  var i; // 重新声明不改变i的值
  console.log(i); // 计数
}
```
js不会对重复声明变量提示错误，它会忽略后续的声明（但若后续有初始化则会执行初始化），匿名函数可用来模仿块级作用域并避免这个问题。
```js
(function(){
  // 此处模仿的就是块级作用域
})();
```
将函数声明包含在一对圆括号中，表示它实际上是一个函数表达式，而紧随其后的另一对圆括号表示立即调用这个函数。

所以将outputNumbers改写如下：
```js
function outputNumbers(count){
  (function(){
    for(var i = 0; i < count; i++){
      console.log(i);
    }
  })
  console.log(i); // 出错
}
```
由于i会在匿名函数执行结束时销毁，所以变量i只能在循环内使用，在匿名函数外部访问会报错。这样就创建了一个块级作用域。

这种立即执行匿名函数模仿块级作用域的方式常用于全局作用域中，避免向全局作用域添加过多的变量和函数。

同时这样可以减少闭包占用的内存，因为没有指向匿名函数的引用，只要函数执行完毕，就可以立即销毁其作用域链。

### 私有变量
严格来说，js没有私有成员的概念，所有对象属性都是共有的，不过，却有私有变量的概念（私有成员和私有变量不是同一概念）。

任何在函数中定义的变量都可以认为是私有变量，因为不能在函数的外部访问这些变量。私有变量包括函数的参数、局部变量、在函数内部定义的其他函数：
```js
function add(num1, num2){
  var sum = num1 + num2;
  return sum;
}
```
在add函数内部，有3个私有变量，num1，num2和sum。在函数内部可以访问这几个变量，但在函数外部则不能访问它们。若在函数内部创建一个闭包，则闭包可通过作用域链访问这些变量。因此就可以创建用于访问私有变量的公有方法。

一般将有权访问私有变量和私有函数的公有方法称为特权方法（privileged method）。有两种在对象上创建特权方法的方式。
第一种是在构造函数中定义特权方法：
```js
function MyObject(){
  // 私有变量和私有函数
  var privateVariable = 10;
  function privateFunction(){
    return false;
  }

  // 特权方法
  this.privilegedMethod = function(){
    privateVariable++;
    return privateFunction();
  }
}
```
这个模式在构造函数内部定义了所有私有变量和函数，然后又继续创建了能够访问这些私有成员的特权方法。能够在构造函数中定义特权方法，是因为特权方法作为闭包有权访问在构造函数中定义的所有变量和函数。
对此例而言，变量privateVariable和函数privateFunction只能通过特权方法privilegedMethod访问。

利用私有变量和特权方法的特性，可以隐藏那些不应该被直接修改的数据：
```js
function Person(name){
  this.getName = function(){
    return name;
  };
  this.setName = function(value){
    name = value;
  };
}

var person = new Person('king');
person.getName(); // 'king'
person.setName('tom');
person.getName(); // 'tom'
```
私有变量name在Person的每一个实例都不相同，因为每次调用构造函数都会重新创建这两个方法，但这样做会有缺点，那就是必须使用构造函数模式来达到目的，而构造函数模式的缺点是针对每个实例都会创建同一组新方法，而使用静态私有变量来实现特权方法可以避免这个问题。

#### 静态私有变量
通过在私有作用域中定义私有函数和变量，同样可以创建特权方法：
```js
(function(){
  // 私有变量和私有函数
  var privateVariable = 10;
  function privateFunction(){
    return false;
  }

  // 构造函数
  MyObject = function(){};

  // 公有/特权方法
  MyObject.prototype.privilegedMethod = function(){
    privateVariable++;
    return privateFunction();
  };
})();
```
这个模式创建了一个私有作用域，并在其中封装了一个构造函数及相应的方法，在私有作用域中，首先定义了私有变量和私有函数，然后又定义了构造函数及其公有方法，公有方法是在原型上定义的，属于典型的原型模式。

需要注意的是，这个模式在定义构造函数时并没有使用函数声明，而是使用了函数表达式，函数声明只能创建局部函数。同时，在声明MyObject时没有使用var关键字，这样MyObject就变成了一个全局变量，能够在私有作用域之外访问到（在严格模式下，给未声明的变量赋值会报错）。

这个模式与在构造函数中定义特权方法的只要区别是，私有变量和函数是由实例共享的，由于特权方法是在原型上定义的，因此，所有实例都使用同一个函数，而这个特权方法，作为一个闭包，总保存着对包含作用域的引用,如下例：
```js
(function(){
  var name = '';

  Person = function(value){
    name = value;
  };

  Person.prototype.getName = function(){
    return name;
  }
  Person.prototype.setName = function(value){
    name = value;
  };
})();

var p1 = new Person('king');
p1.getName(); // 'king'
p1.setName('tom');
p1.getName(); // 'tom'

var p2 = new Person('mark');
p1.getName(); // 'mark'
p2.getName(); // 'mark'
```
上例中，Person构造函数与getName和setName方法一样，都有权访问私有变量name，在这种模式下，变量name就变成了一个静态的、由所有实例共享的属性，即在一个实例上调用setName会影响所有实例，而调用setName或新建一个Person实例都会赋予name属性一个新值，结果就是所有实例都返回相同的值。

以这种方式创建静态私有变量会因为使用原型而复用代码，但每个实例都没有自己的私有变量，所以需要视情况而定。

同时，由于查找作用域链的次数会影响查找速度，所以闭包和私有变量也不是没有缺点。

#### 模块模式
静态私有变量模式主要用于为自定义类型创建私有变量和特权方法。而模块模式（module pattern）则专为单例创建私有变量和特权方法。所谓单例（singleton）指的是只有一个实例的对象，一般，js是以对象字面量的方式来创建单例对象的。
```js
var singleton = {
  name: value,
  method: function(){
    // ...
  }
};
```
模块模式为单例添加私有变量和特权方法：
```js
var singleton = function(){
  // 私有方法和属性
  var privateVariable = 10;
  function privateFunction(){
    return false;
  }

  // 特权/公有方法和属性
  return{
    publicProperty: true,
    publicMethod: function(){
      privateVariable++;
      return privateFunction();
    }
  };
}();
```
模块模式使用一个返回对象的匿名函数，在匿名函数内部，首先定义了私有变量和函数，然后将一个对象字面量作为函数的值返回，返回的对象字面量只包含可以公开的属性和方法，由于这个对象是在匿名函数内部定义的，因此它的公有方法有权访问私有变量和函数。从本质上将，这个对象字面量是单例的公共接口，这种模式在需要对单例进行某些初始化，同时又需要维护其私有变量时非常有用。

```js
var application = function(){
  // 私有
  var components = new Array();

  // 初始化
  components.push(new BaseComponent());

  // 公有
  return {
    getComponentCount: function(){
      return components.length;
    },
    registerComponent: function(component){
      if(typeof component == 'object'){
        components.push(component);
      }
    }
  };
}();
```
在Web应用程序中，经常需要使用一个单例来管理应用程序级的信息，上例创建了一个用于管理组件的application对象。在创建这个对象的过程中，首先声明了一个私有的components数组，并向数组中添加了一个BaseComponent的新实例（不需关心BaseComponent的实现）。而返回对象的getComponentCount和registerComponent方法，都是有权访问数组components的特权方法，前者返回已注册的组件数目，后者用于注册组件。

简而言之，如必须创建一个对象并以某些数据对其进行初始化，同时还要公开一些能够访问这些私有数据的方法，那么就可以使用模块模式。以这种模式创建的每个单例都是Object的实例，因为最终要通过一个对象字面量来表示它。由于单例通常都是作为全局对象存在的，不会通过它传递函数，因此，也没有必要使用instanceof操作符来检查其对象类型。

#### 增强的模块模式
进一步改进模块模式，即在返回对象之前加入对其增强的代码，这种增强的模块模式适合那些单例必须时某种类型的实例，同时还必须添加某些属性或方法对其加以增强的情况。如下：
```js
var singleton = function(){
  // 私有方法和属性
  var privateVariable = 10;
  function privateFunction(){
    return false;
  }

  // 创建对象
  var object = new CustomType();

  // 添加特权/公有方法和属性
  object.publicProperty = true;
  object.publicMethod =  function(){
    privateVariable++;
    return privateFunction();
  }
  return object;
}();
```
又比如前面例子中的application对象必须时BaseComponent实例，那么可以修改为如下：
```js
var application = function(){
  // 私有
  var components = new Array();

  // 初始化
  components.push(new BaseComponent());

  // 创建application的一个局部副本
  var app = new BaseComponent();

  // 公有
  app.getComponentCount = function(){
      return components.length;
  };
  app.registerComponent = function(component){
    if(typeof component == 'object'){
      components.push(component);
    }
  };
  
  return app;
}();
```
修改后的application单例中，主要的不同在于命名变量app的创建过程，因为它必须时BaseComponent的实例，这个实例实际上时application对象的局部变量版，此后，又为app对象添加了能够访问私有变量的公有方法。最后返回app对象，结果仍然时将它赋给全局变量application。

### 小结
在js中，函数表达式非常有用，使用函数表达式可以无需对函数命名，从而实现动态编程，你们函数，也称为拉姆达函数，是一种使用js函数的强大方式，总结函数表达式的特点如下：
- 函数表达式不同于函数声明，函数声明要求有名字，但函数表达式不需要，没有名字的函数表达式也称为匿名函数
- 在无法确定如何引用函数的情况下，递归函数会变得比较复杂
- 递归函数应该始终使用arguments.callee来递归地调用自身，不要使用函数名——函数名可能会发生变化

当函数内部定义了其他函数时，就创建了闭包，闭包有权访问包含函数内部的所有变量，原理如下：
- 在后台执行环境中，闭包的作用域链包含着它自己的作用域、包含函数的作用域和全局作用域
- 通常，函数的作用域极其所有变量都会在函数执行结束后被销毁，但使用函数返回一个闭包时，这个函数的作用域将会一直存在直到闭包被销毁。

使用闭包可以在js中模仿块级作用域（js本身没有块级作用域的概念），要点如下：
- 创建并立即调用一个函数，这样既可以执行其中的代码，又不会在内存留下对该函数的引用
- 立即调用函数的内部的所有变量都会在调用后立即销毁，除非将某些变量赋值给了包含作用域（即外部作用域）中的变量

闭包还可以用于在对象中创建私有变量，特点如下：
- 即使js没有正式的私有对象属性的概念，但可以使用闭包实现公有方法，通过其访问在包含作用域中定义的变量
- 可以使用构造函数模式、原型模式来实现自定义类型的特权方法，也可以使用模块模式，增强的模块模式来实现单例的特权方法

js中函数表达式和闭包都非常有用，利用他们能实现非常多的功能，但过多使用闭包会导致性能下降，因为创建闭包必须维护额外的作用域，会占用大量内存。