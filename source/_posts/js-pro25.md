---
title: JavaScript高级程序设计-25-高级技巧-1
categories: js
tags:
  - js
  - js-pro
date: 2016-09-17 09:36:39
updated:
---

js是一种非常灵活的语言，具有多种使用风格，由于天生的动态属性，还能使用非常复杂和有趣的模式。

### 高级函数
函数是js中最有趣的部分之一，本质非常简单和过程化，但通过语言特性能实现一些非常强大的功能。

#### 安全的类型检查
由于js内置的类型检测机制不是非常完美，尤其是与浏览器的实现相关，比如：
typeof操作符的返回值就受浏览器影响、instanceof操作也存在多个全局作用域的情况的问题，当检查某个对象到底是原生对象还是自定义的对象的时候也有问题，而在Web开发中，能够区分原生与非原生对象非常重要。
因此，想要安全的检测类型，直接提供的原生手段不足以完成，但js中的Object的toString方法提供了这种可能，因为该方法会返回一个表示构造函数名的字符串。
```js
var value = [];
Object.prototype.toString.call(value); // [object Array]
```

#### 作用域安全的构造函数
一个构造函数必须使用new操作符才能正常创建并返回一个新对象实例。当使用new调用构造函数时，构造函数内用到的this对象会指向新创建的对象实例。而若不使用new直接调用则this会被映射到全局对象window上，因为this对象是在运行时绑定的，这样的后果有可能会导致window中属性被覆盖而出错。

而解决此问题的方法就是创建一个作用域安全的构造函数，即，在进行创建/修改对象前，先确定this对象 是否为正确类型的实例，若不是则创建新的实例返回。
```js
function Person(name){
  if(this instanceof Person){
    this.name = name;
  }else{
    return new Person(name);
  }
}
var p1 = new Person('king');
p1.name; // king
var p2 = Person('tom');
p2.name; // tom

window.name; //undefined
```
如此，上述代码无论在调用Person构造函数时是否用new操作符，都会返回一个Person的新实例，避免了在全局对象上意外设置/修改属性。

但这样锁定构造函数的环境后就无法使用构造函数窃取模式的继承了:
```js
function Person(name){
  if(this instanceof Person){
    this.name = name;
  }else{
    return new Person(name);
  }
}

function Doctor(name, age){
  Person.call(this, name);
  this.age = age;
}

var p1 = new Doctor('king', 21);
p1.name; // undefined
```
因为Doctor构造函数中，此时的this不是Person的实例，Person的构造函数返回的是一个新的Person实例，所以新创建的Doctor中的this作用域并没有能继承到Person中的name属性，而`Person.call(this, name);`返回的新实例也没有被用到。

但上述够战术窃取模式的问题可以通过原型链解决，将Doctor构造函数的显式原型设置为Person的实例即可：
```js
Doctor.prototype = new Person();
var p1 = new Doctor('king', 21);
p1.name; // king
```

#### 惰性载入函数
所谓的惰性载入函数是为解决一些函数执行需要做if-else判断语句用于判断执行环境，能力检测等，而这些判断完全可以只在初始执行依次的判断，但这些函数每次执行都会大量冗余重复的判断环境，这些测试都是没必要的。解决方法就是惰性载入技术。

惰性载入表示函数执行分支仅会发生依次，有2种实现惰性载入的方式，第一种是在函数被调用时在处理函数，在第一次调用的过程中，该函数会被覆盖为另一个按合适方式执行的函数，这样任何对原函数的调用都不用在经过执行的分支了。例如下面创建XHR的函数，在第一次执行完成后就不会在进行if-else的能力判断了：
```js
function createXHR(){
  if(typeof XMLHttpRequest != 'undefined'){
    createXHR = function(){
      return new XMLHttpRequest();
    };
  }else{
    createXHR = function(){
      throw new Error('no XHR object available');
    };
  }
}
```

第二种实现的方式是在声明函数时就指定适当的函数，这样，第一次调用函数是就不会损失性能，而在代码首次加载时会损失一点性能,比如使用一个自执行的匿名函数用于确定该使用哪个函数实现：
```js
var createXHR = (function(){
    if(typeof XMLHttpRequest != 'undefined'){
      return function(){
        return new XMLHttpRequest();
      };
    }else{
      return function(){
        throw new Error('no XHR object available');
      };
    }
})()
```

两种方式都非常有用，而那种方式更好取决于具体需求。

#### 函数绑定
函数绑定指的是在特定的this环境中指定参数调用另一个函数，这种技巧常常和回调函数与事件处理程序一起使用，以便将函数作为变量传递的同时保留代码执行环境。
```js
var handler = {
  msg: 'handled',
  handleClick: function(event){
    console.log(this.msg);
  }
}
var btn = document.getElementById('myBtn');
EventUtil.addHandler(btn, 'click', handler.handleClick); // undefined
```
上述代码之所以执行addHandler绑定后的handler.handleClick会输出undefined是由于handler.handleClick的执行环境没有保存，因为btn在被点击时，this指向了DOM而非handler对象。

可以用闭包解决执行环境保存的问题：
```js
EventUtil.addHandler(btn, 'click', function(event){
  handler.handleClick(event);
}); // handled
```
闭包中直接调用`handler.handleClick(event);`这样handleClick的执行环境为handler对象，所以最后输出handled。但闭包也有缺点，即难于理解和调试。因此很多js库都实现了可以将函数绑定到指定环境的函数，即bind函数（ES5也原生支持此方法了）。
```js
function bind(fn, context){
  return function(){
    return fn.apply(context, arguments);
  }
}
```
在bind函数中创建一个闭包，闭包使用apply调用传入的fn函数，并给apply传递context对象和内部函数的arguments参数，这样当调用返回的函数时，它会在给定的context环境中执行fn函数并使用传入的所有参数。
```js
EventUtil.addHandler(btn, 'click', bind(handler.handleClick, handler)); // handled
```

ES5的原生方式类似，而且是作为函数的方法,支持此方法的浏览器有IE9+、Firefox4+、Chrome：
```js
EventUtil.addHandler(btn, 'click', handler.handleClick.bind(handler)); // handled
```

只要是将某个函数指针以值的形式进行传递，同时该函数必须在特定环境中执行，被绑定函数的效用就凸显出来了，它们主要用于事件处理程序以及定时器，然而，被绑定函数与普通函数相比有更多开销，需要更多内存，同时也因为多重函数调用导致稍微慢一些，所以最好仅在必要时使用。

#### 函数柯里化
与函数绑定的紧密相关主题是函数柯里化（function currying）,它用于创建已经设置好了一个或多个参数的函数，函数柯里化基本方法和函数绑定是一样的，使用一个闭包返回一个函数，两者的区别在于，当函数被调用时，返回的函数还需要设置一些传入的参数：
```js
function curry(fn, context){
  var args = Array.prototype.slice.call(arguments, 2);
  return function(){
    var innerArgs = Array.prototype.slice.call(arguments);
    var finalArgs = args.concat(innerArgs);
    return fn.apply(context, finalArgs);
  }
}
// 不需要绑定执行上下文，调用
function add(n1, n2){
  return n1 + n2;
}
var curriedAdd = curry(add, null, 5); // 由于不需要绑定执行上下文，所以传入null

// 需要绑定执行上下文，调用
var handler = {
  msg: 'handled',
  handleClick: function(name, event){
    console.log(name, this.msg);
  }
}

EventUtil.addHandler(btn, 'click', curry(handler.handleClick, handler, 'my-btn')); // my-btn, handled
```
curry函数的主要工作就是将返回函数的参数进行排序，第一个参数是要进行柯里化的函数，第二个参数为要绑定的上下文执行环境，其他参数为要传入的值，为了获取第二个参数后的所有其他参数，在arguments对象上调用slice方法，并传入2表示将返回的数组包含从第二个参数开始的所有参数，然后args数组包含了来自外部函数的参数。在内部函数中创建了innerArgs数组用来存所有传入的参数，有了存放来自外部和内部函数和的参数数组后，就可以使用concat方法将它们组合为finnalArgs，然后用apply方法将结果传递给该函数。

ES5的bind方法也实现了柯里化，只要在this的值后在传入其他参数即可。
```js
EventUtil.addHandler(btn, 'click', handler.handleClick.bind(handler, 'my-btn')); // my-btn, handled
```

### 防篡改对象
ES5可以自定义防篡改对象（tamper-proff object）,主要为了防止以外修改代码或用不兼容的功能重写原生对象。

涉及到对象属性的`[[Confrigurable]]`、`[[Writable]]`、`[[Enumerable]]`、`[[Value]]`、`[[Get]]`、`[[Set]]`特性，而一旦将对象定义为防篡改对象就无法撤销。

同时，一般在非严格模式下非法操作会忽略，严格模式下会报错。

#### 不可扩展对象
ES5为对象定义的第一个保护级别。

默认情况下，所有对象都是可扩展的，即任何时候都可以向对象中添加属性和方法，通过Object.preventExtensions(obj)方法可以改变这个行为，执行此方法后就无法给obj对象添加属性和方法了，但仍然可以修改和删除已有成员。Object.isExtensible()方法可以确定对象是否可扩展。

#### 密封的对象
ES5为对象定义的第二个保护级别。通过Object.seal(obj)密封某个对象。Object.isSealed()用于检测对象是否被密封。

密封对象（sealed object）不可扩展而且已有成员的`[[Confirgurable]]`特性会被设置为false，即无法使用Object.definedProperty()把数据属性修改为访问器属性，也就无法删除属性和方法了。但属性值可修改。

被密封的对象不可扩展，所以isExtensible方法返回false，isSealed返回true。

#### 冻结的对象
冻结对象（frozen object）是最严格的防篡改级别，冻结的对象既不可扩展，又是密封的，而且对象属性的`[[Writable]]`特性会被设置为false，若定义`[[Set]]`函数，则访问器属性仍然可写的。

ES5定义Object.freeze()方法用来冻结对象。用Object.isFrozen()检测冻结对象。
