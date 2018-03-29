---
title: JavaScript高级程序设计-12-面向对象3-继承
categories: JavaScript
tags:
  - JavaScript
  - JavaScript高级程序设计
date: 2016-08-11 08:22:40
updated:
---

接上篇[JavaScript高级程序设计-12-面向对象2-创建对象](/2016/08/11/js-pro12)

### 继承
继承是OO语言中的一个最为经典的概念之一，许多OO语言都支持两种继承方式：接口继承和实现继承。接口继承只继承方法签名，而实现继承则继承实际的方法。由于函数没有签名，在ES中无法实现接口继承。ES只支持实现继承，主要依靠原型链来实现的。

#### 原型链
ES中原型链作为实现继承的主要方法，其基本思想是利用原型让一个引用类型继承另一个引用类型的属性和方法。由构造函数、原型、实例之间的关系：每个构造函数都有一个原型对象，原型对象都包含一个指向构造函数的指针，而实例都包含一个指向原型对象的内部指针。

实现原型链有一种基本模式:
```js
// 父类
function SuperType(){
  this.property = true;
}
SuperType.prototype.getSuperValue = function(){
  return this.property;
};

// 子类
function SubType(){
  this.subproperty = false;
}
// 继承自SuperType
SubType.prototype = new SuperType();
SubType.prototype.getSubValue = function(){
  return this.subproperty;
};

var instance = new SubType();
console.log(instance.getSuperValue()); // true
```
通过将SuperType的实例赋给SubType.prototype实现了SubType继承SuperType。本质是重写原型对象，用一个新类型的实例替代子类的原型。因此，原存在于SuperType的实例中的所偶属性和方法，现在也存在于SubType.prototype中了。在确定了继承关系后，给SubType.prototype添加了一个方法，这样在继承了SuperType的属性和方法基础上又添加了一个新方法。
![SuperType&SubType](1.png)
如图，instance指向SubType的原型，SubType的原型指向SuperType的原型。getSuperValue方法仍然还在SuperType.prototype中，但property则位于SubType.prototype中。因为property是一个实例属性，而getSuperValue则是一个原型方法，既然SubType.prototype是SuperType的实例，那么property自然位于该实例中。
此外，需要注意instance.constructor指向的是SuperType,因为原本SubType的原型指向了另一个对象，即SuperType的原型，而这个原型对象的constructor属性指向SuperType。

实现原型链，本质上扩展了原型搜索机制。当以读取模式访问一个实例属性时，首先会在实例中搜索该属性，若没有找到该属性，则会继续搜索实例的原型。在通过原型链实现继承的情况下，搜索过程就得以沿着原型链继续向上。在找不到属性或方法的情况下，搜索过程总是要一环一环的找，直到原型链末端才会停止。

以上例来说，调用instance.getSuperValue会经历三个搜索步骤：
1. 搜索实例
2. 搜索SubType.prototype
3. 搜索SuperType.prototype(上例在此处找到该方法)

##### 默认原型
事实上，前面图中的原型链少了一环。由于所有引用类型都继承了Object，且这个继承也是通过原型链实现的，所以所有的函数的默认原型都是Object的实例，因此默认原型都会包含一个内部指针，指向Object.prototype。这就是所有自定义类型都会继承toString、valueOf等默认方法的根本原因。

完整的原型链示意图应为：
![Full Prototype Chain](2.png)

##### 确定原型和实例的关系
可以通过两种方式来确定原型和实例之间的关系，第一种方式是使用instanceof操作符，测试实例与原型链中出现过的构造函数的关系。
```js
instance instanceof Object; // true
instance instanceof SuperType; // true
instance instanceof Subtype; // true
```
由于原型链的关系，可以说instance是Object、SuperType或SubType中任何一个类型的实例。

第二种方法是使用isPrototypeOf方法，同样，只要是原型链中出现过的原型，都可以认为该原型链所派生的实例的原型，因此isPrototypeOf方法也会返回true:
```js
Object.prototype.isPrototypeOf(instance); // true
SuperType.prototype.isPrototypeOf(instance); // true
Subtype.prototype.isPrototypeOf(instance); // true
```

##### 谨慎定义方法
子类有时候需要重写父类的方法，或需要添加父类中不存在的方法，无论如何，给原型添加方法一定要放在替换原型的语句之后。

同时需要注意，通过原型链实现继承时，不能使用对象字面量创建原型方法，即防止重写原型链。

##### 原型链问题
通过原型链实现继承很强大，但也存在一些问题，其中，主要是包含引用类型值时的共享问题。即包含引用类型值的原型属性会被所有实例共享，这就是为什么推荐在构造函数中，而不是原型对象中定义属性的原因。通过原型来实现继承时，原型实际上会变成另一个类型的实例，于是，原先的实例属性也就顺理成章的变成了现在的原型属性。
```js
function SuperType(){
  this.colors = ['red','green'];
}
function SubType(){}

SubType.prototype = new SuperType();

var i1 = new SubType();
i1.colors.push('blue');

var i2 = new SubType();
i1.colors; // 'red,green,blue'
i2.colors; // 'red,green,blue'
```
原型链的第二个问题是，在创建子类的实例时，不能向父类的构造函数中传递参数，实际上，应该说是没有办法在不影响所有对象实例的情况下，给父类的构造函数传递参数。

因此，基于上述两点，很少单独使用原型链继承。

#### 借用构造函数
在解决原型中包含引用类型值所引发的问题时，开发者开始使用借用构造函数（constructor stealing）的技术（也被称为伪造对象或经典继承）。思想很简单，即在子类构造函数的内部调用父类构造函数。因为函数只不过是在特定环境下执行代码的对象，因此通过使用apply和call方法也可以在新创建的对象上执行构造函数：
```js
function SuperType(){
  this.colors = ['red','green'];
}
function SubType(){
  SuperType.call(this); // 继承SuperType
}

var i1 = new SubType();
i1.colors.push('blue');
i1.colors; // 'red,green,blue'

var i2 = new SubType();
i2.colors; // 'red,green'
```
`SuperType.call(this);`通过使用call/apply方法借调了父类的构造函数，实际上是在新创建的SubType实例的环境下调用了SuperType构造函数，这样依赖，就会在新的SubType对象上执行SuperType函数中定义的所有对象初始化代码，因此，SubType的每个实例都会有自己的colors属性的副本。

##### 传递参数
相对于原型链而言，借用构造函数有一个很大的优势，即可以在子类构造函数中向父类构造函数传递参数：
```js
function SuperType(name){
  this.name = name;
}
function SubType(name){
  SuperType.call(this, name); // 继承SuperType,并传递参数

  this.age = 22;
}

var i1 = new SubType('king');
i1.name; // 'king'
i1.age; // 22
```

##### 借用构造函数的问题
若仅仅是借用构造函数，那么无法米变构造函数模式存在的问题，即方法都在构造函数中定义，因此无法复用。而且，在父类的原型中定义的方法对子类是不可见的，结果所有类型都只能使用构造函数模式。所以，很少单独使用借用构造函数的技术。

#### 组合继承
组合继承（combination inheritance）也叫伪经典继承，指的是将原型链和借用构造函数的技术组合到一起，从而发挥两者优势。其思路是通过使用原型链实现对原型属性和方法的继承，通过借用构造函数来实现对实例属性的继承，这样既通过在原型上定义方法实现了函数复用，又能保证每个实例都有它自己的属性。
```js
function SuperType(name){
  this.name = name;
  this.colors = ['red','green'];
}
SuperType.prototype.sayName = function(){
  console.log(this.name);
}

function SubType(name, age){
  SuperType.call(this, name); // 继承SuperType的属性,并传递参数
  this.age = age;
}

SubType.prototype = new SuperType(); // 继承方法
SubType.prototype.sayAge = function(){
  console.log(this.age);
}

var i1 = new SubType('king', 22);
i1.colors.push('blue');
i1.colors; // 'red,green,blue'
i1.sayName; // 'king'
i1.sayAge; // 22


var i2 = new SubType('tom', 21);
i1.colors; // 'red,green'
i2.sayName; // 'tom'
i2.sayAge; // 21
```
组合继承是js中最常用的继承模式，避免了原型链和借用构造函数的缺陷，融合了两者优点，同时instanceof和isPrototypeOf也能用于识别基于组合继承创建的对象。

#### 原型式继承
Douglas Crockford介绍了一种实现继承的方法，这种方法并没有严格意义上的构造函数，思想是借助原型基于现有对象创建新对象，同时还不必创建自定义类型：
```js
function obj(o){
  function F(){}
  F.prototype = o;
  return new F();
}
```
在obj函数内部，先创建一个临时的构造函数，然后将传入的对象作为这个构造函数的原型，最后返回这个临时类的新实例，从本质上， obj对传入其中的对象执行了依次浅复制，如下例:
```js
var p1 = {
  name: 'king',
  links: ['tom','jim']
};

var p2 = obj(p1);
p2.name = 'max';
p2.links.push('bob');

var p3 = obj(p1);
p3.name = 'mark';
p3.links.push('alice');

p1.links; // 'tom,jim,bob,alice'
```
从上例来看，原对象中的引用类型的值会被所有新对象共享，实际上，这相当于创建了p1的两个副本。

ES5新增Object.create方法规范了原型式继承。该方法接收两个对象参数：第一个参数作为新对象原型，第二个参数是可选的，为新对象定义额外属性。在只传入一个参数的情况下，Object.create和obj的行为相同。
```js
var p1 = {
  name: 'king',
  links: ['tom','jim']
};

var p2 = Object.create(p1);
p2.name = 'max';
p2.links.push('bob');

var p3 = Object.create(p1);
p3.name = 'mark';
p3.links.push('alice');

p1.links; // 'tom,jim,bob,alice'
```
Object.create方法的第二个参数与Object.defineProperties方法的第二个参数格式相同，每个属性都通过自己的描述符定义，以这种方式指定的任何属性都会覆盖原型对象上的同名属性：
```js
var p1 = {
  name: 'king',
  links: ['tom','jim']
};

var p2 = Object.create(p1,{
  name:{
    value: 'max'
  }
});
p2.name; // max
```

#### 寄生式继承
寄生式（parasitic）继承是与原型式继承紧密相关的一种思路，并且同样也是由Crockford推广的。寄生式继承的思路与寄生构造函数和工厂模式类似，即创建一个仅用于封装继承过程的函数，该函数在内部以某种方式来增强对象，最后返回对象即可:
```js
function createObj(original){
  var clone = obj(original); // 通过调用函数创建一个新对象
  clone.sayHi = function(){ // 在对象上添加属性/方法
    console.log('hi');
  };
  return clone; // 返回对象
}

// 使用
var p1 = {
  name: 'king',
  links: ['tom','jim']
};

var p2 = createObj(p1);
p2.sayHi(); // hi
```
若主要考虑对象而不是自定义类型和构造函数时，寄生式继承是一种有用的模式，任何能够返回新对象的函数都适用此模式。

同时需要注意，与构造函数模式类似，通过寄生式继承为对象添加函数不能做到函数复用，会降低效率。

#### 寄生组合式继承
组合继承是js最常用的继承模式，但也有不足，即无论如何，组合继承一定会调用两次父类构造函数，一次是在创建子类原型时，另一次是在子类构造函数内部。由于子类最终会包含父类对象的全部实例属性，但不得不在调用子类构造函数时重写这些属性：
```js
function SuperType(name){
  this.name = name;
  this.colors = ['red','green'];
}
SuperType.prototype.sayName = function(){
  console.log(this.name);
}

function SubType(name, age){
  SuperType.call(this, name); // 第二次调用SuperType()
  this.age = age;
}

SubType.prototype = new SuperType(); // 第一次调用SuperType()
SubType.prototype.constructor = SubType; // 将SubType原型的constructor指向正确的SubType
SubType.prototype.sayAge = function(){
  console.log(this.age);
}
```
第一次调用SuperType()后，SubType.prototype会得到两个name和colors属性，它们都是SuperType的实例属性，现在位于SubType的原型中。当调用SubType的构造函数时，会第二次调用SuperType()，第二次会在新对象上创建实例属性name和colors，同时会屏蔽原型中的两个同名属性。
![两次调用父类构造函数示意图](3.png)
如图所示，由于调用两次SuperType构造函数，所以其实有两组name和colors属性，一组在实例上，一组在SubType原型上。

所谓寄生组合式继承，即通过借用构造函数来继承属性，通过原型链来继承方法。思路为：由于仅需要父类原型的一个副本，所以不必为了指定子类型的原型而调用父类的构造函数。本质上就是通过使用寄生式继承来继承父类的原型，在将结果指定给子类的原型：
```js
function inheritPrototype(subType, superType){
  var proto = obj(superTpye.prototype); // 创建对象
  proto.constructor = subType; // 增强对象
  subType.prototype = proto; // 指定对象
}
```
inheritPrototype函数实现了寄生式组合继承的最简单形式，此函数接收两个参数：子类构造函数和父类构造函数。在函数内部，第一步是创建父类原型的一个副本，第二步是为创建的副本添加constructor属性，从而纠正由于重写原型而失去的默认的constructor属性，最后一步将新创建的副本对象赋值给子类的原型。

如此，通过调用inheritPrototype去替换前例中为子类原型赋值的语句：
```js
function SuperType(name){
  this.name = name;
  this.colors = ['red','green'];
}
SuperType.prototype.sayName = function(){
  console.log(this.name);
}

function SubType(name, age){
  SuperType.call(this, name);
  this.age = age;
}

inheritPrototype(SubType, SuperType); // 此处替换new SubType语句

SubType.prototype.sayAge = function(){
  console.log(this.age);
}
```
上例只调用了依次SuperType构造函数，并且因此避免了在SubType.prototype上创建不必要的、多余的属性，同时原型链还是保持不变，也能正常使用instanceof和isPrototypeOf方法。

至此，引用类型最理想的继承模式为寄生组合式继承，YUI的YAHOO.lang.extend()方法就采用了寄生组合继承。

### 小结
ES支持OO，但不使用类或接口。对象可以在代码执行过程中创建或增强，因此具有动态性而非严格定义的实体。在没有类的情况下，可采用下列模式创建对象：
- 工厂模式，使用简单的函数创建对象，为对象添加属性或方法，然后返回对象，这个模式后被构造函数模式所取代
- 构造函数模式，可以创建自定义引用类型，可以像创建内置对象实例一样使用new操作符，但缺点是属性和方法无法得到复用。由于函数可以不局限于任何对象（与对象具有松散耦合的特点），因此没有理由不在多个对象间共享函数。
- 原型模式，使用构造函数的prototype属性来指定那些是应该共享的属性和方法，组合使用构造函数模式和原型模式时，使用构造函数定义实例属性，而使用原型定义共享的属性和方法。

JS主要通过原型链实现继承，原型链的构建是通过将一个类型的实例赋值给另一个构造函数的原型实现的，这样，子类就能够访问父类的所有属性和方法，这一点与基于类的继承很类似。

原型链的问题是对象实例共享所有继承的方法和属性，因此不适合单独使用，解决问题的技术是借用构造函数，即在子类构造函数内部调用父类构造函数，这样能使每个实例拥有自己的属性，同时还能保证只使用构造函数模式来定义类型，使用最多的继承模式是组合继承，这种模式使用原型链继承共享的属性好方法，而通过借用构造函数继承实例属性。
此外可选的继承模式还有：
- 原型链继承，可以在必须预先定义构造函数的情况下实现继承，本质是执行对给定对象的浅复制，而复制得到的副本还可以进一步改进。
- 寄生式继承，与原型式继承非常相似，也是基于某个对象或某种信息创建一个对象，然后增强对象，最后返回对象。为了解决组合继承由于多次调用父类构造函数而导致的低效问题，可以将这个模式与组合继承一起使用。
- 寄生组合式继承，实现基于类型继承的最有效方式，集寄生式继承和组合继承优点于一身。