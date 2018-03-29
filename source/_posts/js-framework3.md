---
title: JavaScript框架设计笔记-3-类工厂
categories: JavaScript
tags:
  - JavaScript
  - JavaScript框架设计
date: 2016-12-18 17:25:38
updated:
---

直到ES6之前，JS没有真正传统的类，但可以模拟实现，所以类工厂是很多框架的标配，本篇主要学习各种类的实现。（PS:其实ES6中的`class`是一个语法糖，看着像传统的类而已，本质没变过，- -。）

#### JS对类的支持
传统OO语言中，类的实例都通过构造函数new出来，JS存在new操作符，并且它的所有函数都作可以为构造器。

JS从其他语言借鉴了原型机制，prototype作为一个特殊的对象属性存在每一个函数上，当一个函数通过new操作符“创建”出“实例”，这个实例对象就拥有了这个函数的prototype对象所有一切成员，从而实现所有实例对象都共享一组方法或属性。JS的类通过修改这个prototype对象，以区别原生对象及其他自定义“类”。比如在浏览器中，Node类基于Object修改而来，而Element则基于Node，HTMLElement基于Element....

一般创建自己的类实现重用和共享：
```js
function A(){} // 外观上，构造器和普通函数没有什么区别。习惯上大写首字母

A.prototype = {
  aa: 'aa', // 原型属性
  method: function(){} // 原型方法
};

var a = new A; // 创建实例a
var b = new A; // 创建实例b

console.log(a.aa === b.aa); // true
console.log(a.method === b.method); // true
```

一般把定义在原型上的成员（方法/属性）叫原型成员，它为所有实例所共享。直接在构造器（通过this指定或直接var声明）内指定的方法叫特权成员，这些特权成员每一个实例一个副本，不会相互影响，因此通常把共享的用于操作数据的方法放在原型，把私有数据放在特权属性中。但若放在this上，则实例可在任何时候直接访问，当放在函数体内的作用域而不是this上时，就变成了私有属性（通过实例也无法访问，只有通过实例的方法能访问）。

```js
function A(){
  var count = 0; // 私有属性
  this.aa = 'aa'; // 特权属性
  this.method = function(){ return count; }; // 特权方法
  this.obj = {}; // 特权属性
}

A.prototype = {
  aa: 'aa',
  method: function(){}
};

var a = new A;
var b = new A;

console.log(a.aa === b.aa); // true， 由于aa的值为基本类型，所以比较的是原始值，但其实有不同的内存地址
console.log(a.obj === b.obj); // false 由于obj的值为引用类型，所以比较的是内存地址，可以看出是不同的对象
console.log(a.method === b.method); // false，函数其实也是特殊的对象，同引用类型的规则

delete a.method;
console.log(a.method === A.prototype.method); // true
```
上例中的特权成员将同名的原型成员遮盖了，若将这些特权的成员删除，就又能访问原型成员了。

在Java中，原型方法与特权方法都被归为实例方法（即通过实例访问的，而不是通过类访问的），在Java中，还有一种类成员的东西，是通过类访问的，实例无法访问。JS中模拟则直接在类上定义即可：
```js
A.method2 = function(){ console.log('A.method2') }; // 模拟Java的类方法

var c = new A;
console.log(c.method2); // undefined
A.method2(); // A.method2
```

继承的实现：即，只要prototype有啥，实例就有啥，无论这个成员是什么时候添加的。而若将prototype对象赋值为另一个类的原型（prototype），那么就能将另一个类的所有原型成员“偷”（或者叫“继承”）过来。
```js
function A(){}
A.prototype = {
  aaa: 1
}

function B(){}
B.prototype = A.prototype;

var b = new B;
console.log(b.aaa); // 1

A.prototype.bbb = 2;
console.log(b.bbb); // 2
```
由于prototype引用的是相同对象，所以修改A类的原型（prototype属性），也等同于修改了B类的原型。而传统的继承却不是这样的，修改子类的原型不应该影响到父类原型，因此最好不要将一个原型对象赋给两个类。方法一，通过`for in`把父类的原型成员逐一赋给子类的原型（简单粗暴，直接添加/覆盖到子类原型）；方法二，子类的原型不是直接指向父类原型，而是先将此父类的原型赋给一个函数的原型，然后将这个函数的实例作为子类的原型（这样通过这个实例可以获取父类的原型成员，而且可以一直追溯到Object）。

方法一，通常是先自定义一个mixin(也叫extend)方法，该方法很简单，就是将一个对象中所有的属性都copy到另一个对象中，但问题是无法使用`instanceof`操作符获取是否是某类的实例的判断了（对传统继承而言，是否是某类的实例是非常重要的，许多设计模式或者判断需要依据类实例判断结果）。
```js
// 使用时，传入的都是原型，将source中的成员都添加到destination，但会覆盖同名成员
function extend(destination, source){
  for(var property in source){
    destination[property] = source[property];
  }
  return destination;
}
```

方法二，被称为原型继承的经典方法。
```js
function A(){}
A.prototype = {
  aa: function(){ alert(1) },
  aaa: 1
}

function bridge(){}
bridge.prototype = A.prototype;

function B(){}
B.prototype = new bridge; // 此处的new bridge操作得到了一个实例，
// 通过该实例能访问父类的原型成员，因为该实例的类的原型就是父类原型，
// 而在子类原型（其实是一个父类原型的实例）上添加新成员
// PS: 此处其实还有一个B.prototype.constructor的指向问题
//    实例的constructor（其实是创建一个函数的时候就自动添加到原型上的）指向其构造器
//    可参考：[深入分析js中的constructor 和prototype](https://www.cnblogs.com/yupeng/archive/2012/04/06/2435386.html)

var a = new A;
var b = new B;

console.log(A.prototype == B.prototype); // false,说明子类和父类的原型对象不是同一个对象了，成功分离
console.log(a.aa === b.aa); // true,子类共享父类的原型方法

A.prototype.bb = function(){ alert(2) } // 为父类动态添加原型方法
console.log(a.bb === b.bb); // true,子类总会共享父类的原型成员

B.prototype.cc = function(){ alert(3) } // 为子类动态添加原型方法
console.log(a.cc === b.cc); // false,父类不会获取到子类修改后的原型成员

// instanceof操作符能正确检测是否是实例
console.log(b instanceof A); // true
console.log(b instanceof B); // true
```
以上的实现方式其实在ES5中已经内置了，即Object.create方法。其原理类似上面的方法二，实现如下（排除第二个参数后的实现）：
```js
// 传入的o是一个原型对象（其实也可以是任意普通的对象）
// 返回子类的原型
Object.create = function(o){
  function F(){} // 相当于bridge函数，
  F.prototype = o;
  return new F();
}

B.prototype = Object.create(A.prototype); // 如此即可指定B的原型为A的原型的一个实例，即B继承A
```

但上面的方法二遗漏了一些东西，当父类有类成员和特权成员时，由于不是定义在父类原型中的，所以方法二的子类原型无法获取到，但传统的类中，子类其实是可以获取到父类的特权成员的（私有成员无法获取）。

在JS中的原型继承没有让子类获取到父类的类成员和特权成员，只能手动添加，这样就需要用到上面的方法一了，特权成员可以在子类的构造器中通过apply实现（即绑定this到父类上）。
```js
//
function inherit(init, Parent, proto){
  // 声明一个构造器，即真正的子类
  function Son(){
    Parent.apply(this, argument); // 先继承父类的特权成员
    init.apply(this, argument); // 再执行自己的构造器
  }

  // 由于Object.create可能不是原生的，因此避免使用第二个参数,而是用一个空对象代替
  Son.prototype = Object.create(Parent.prototype, {});
  // IE下子类无法自动通过父类原型实例获取到父类的toString和valueOf方法
  Son.prototype.toString = Parent.prototype.toString;
  Son.prototype.valueOf = Parent.prototype.valueOf;
  Son.prototype.constructor = Son; // 此处确保构造器的正确指向，而不是Object，

  extend(Son.prototype, proto); // 向子类添加自定义的原型成员
  extend(Son, Parent); // 向子类添加父类的类成员

  return Son; // 最后返回子类
}
```

当访问实例的一个属性时，先找其特权成员，有则返回，没有就找原型，再没有则找父类的原型，直到Object，这就是实例的属性查找机制（即回溯机制）

对属性查找机制的测试如下：
```js
function A(){}
A.prototype = {
  aa: function(){ console.log(1) }
}

var a = new A;
console.log(a.aa); // ƒ (){ console.log(1) } 即 function(){ console.log(1) }

// 将A的整个原型换掉
A.prototype = {aa: 2};
console.log(a.aa); // 不影响，还是ƒ (){ console.log(1) }


// 测试是否能被constructor修改
function B(){}
B.prototype = {
  aa: 3
}
a.constructor = B;
console.log(a.aa); // 不影响
```
上述的测试可以发现，无论修改类原型还是实例的constructor属性，都无法影响到实例查找某个原型上的属性，即回溯查找机制不是通过上面的prototype和constructor属性实现的。

ECMA规定每一个对象都有一个内部属性`[[Prototype]]`，它保存着当new该对象时构造器所引用的prototype指向，在浏览器中对象有一个属性`__proto__`可以访问这个内部属性，而这个属性就是回溯机制的关键，只要不动`__proto__`，实例的属性查找永远不会改变。

使用new操作符时发生的操作如下：
1. 创建一个空对象instance
1. `instance.__proto__ = instanceClass.prototype`
1. 设置构造器函数里的`this = instance`
1. 执行构造器函数中的代码
1. 判断是否有返回值，没有返回值默认为undefined，有引用类型的返回值则返回该引用，否则返回this

验证如下：
```js
function A(){
  console.log(this.__proto__.aa); // 1
  this.aa = 2
}
A.prototype = {
  aa: 1
}

var a = new A;
console.log(a.aa); // 2
a.__proto__ = {
  aa: 3
}

delete a.aa; // 删除特权属性，暴露原型链上的同名属性
console.log(a.aa); // 3
```

有了`__proto__`属性句可以将原型继承变得简洁：
```js
function A(){}
A.prototype = {
  aa: 1
}

function B(){}
B.prototype.__proto__ = A.prototype;

var b = new B;
console.log(b.aa); // 1
console.log(b.constructor); // ƒ B(){} 即 function B(){}
console.log(b instanceof A); // true
console.log(b instanceof B); // true
console.log(b.__proto__ === B.prototype); // true
```

相当于做了如下操作：
```js
function A(){}
A.prototype = {
  aa: 1
}

function bridge(){}
bridge.prototype = A.prototype;

function B(){}
B.prototype = new bridge;
B.prototype.constructor = B;

var b = new B;
B.prototype.cc = function(){ alert(3); };
console.log(b.__proto__ === B.prototype); // true
console.log(b.__proto__.__proto__ === A.prototype); // true 父类的原型对象
```
因为`b.__proto__.constructor`指向`B`,而B的原型（`B.prototype`）是从bridge中得到的（是bridge的一个实例），而`bridge.prototype = A.prototype`。反过来，在定义时，让`B.prototype.__proto__ = A.prototype`就能轻松实现原型继承了。

#### 各种类工厂的实现
由于主流框架类工厂的实现太依赖于各种庞杂的工具函数，而一个精巧的类工厂不过百行左右，只要传入相应的参数或按一定简单格式就能创建一个类。

##### P.js
[https://github.com/jneen/pjs](https://github.com/jneen/pjs)

在调用父类的同名方法时，直接将父类的原型给出，省了_super的过程。

##### JS.Class
[https://github.com/dkraczkowski/js.class](https://github.com/dkraczkowski/js.class)

通过父类构造器的extend方法来产生自己的子类，里面存在一个开关，防止在生成类时无意执行construct方法。

在创建子类时，不通过中间的函数来断开双方的原型链，而是使用父类的实例来做子类的原型。

##### simple-inheritance
[https://github.com/html5crew/simple-inheritance](https://github.com/html5crew/simple-inheritance)

特点是方法链的实现非常优雅，节俭！

##### def.js
[https://github.com/tobytailor/def.js](https://github.com/tobytailor/def.js)

体现JS的灵活性，在形式上模拟Ruby继承，让学过Ruby的人一眼看出哪个是父类，哪个是子类。

#### ES5属性描述符对OO库的影响
ES5中为对象引入属性描述符，能对属性进行更精细的控制，比如，属性是否可以修改，是否可以在for in循环中枚举出来，是否可以删除等。

Object提供的新方法如下：
- Object.keys
- Object.getOwnPropertyNames
- Object.getPrototypeOf
- Object.defineProperty
- Object.defineProperties
- Object.getOwnPropertyDescriptor
- Object.create
- Object.seal
- Object.freeze
- Object.preventExtensions
- Object.isSealed
- Object.isFrozen
- Object.isExtensible

关于如上方法的介绍和基本用法：[MDN/Object](https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Object)

Object.create让原型继承更方便了，但在增加子类的专有原型成员或类成员时，若它们的属性的enumerable为false，单纯的for in循环已经不管用了，就需要用到Object.getOwnPropertyNames, 另外，访问器属性的复制只有通过Object.getOwnPropertyDescriptor和Object.defineProperty才能完成。