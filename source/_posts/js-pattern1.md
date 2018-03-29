---
title: JS设计模式-1-基础复习-面向对象，this
categories: JavaScript
tags:
  - JavaScript
  - 设计模式
date: 2017-11-01 20:15:13
updated:
---

学习设计模式的之前需要复习一些面向对象的基础知识、this等重要概念，以及一些函数式编程的技巧。

### 面向对象的JS
JavaScript没有提供传统面向对象语言中的类式继承，而是通过原型委托的方式来实现对象与对象之间的继承。JavaScript也没有在语言层面提供对抽象类和接口的支持。

#### 动态类型语言和鸭子类型

编程语言按照数据类型大体可以分为两类，一类是静态类型语言，另一类是动态类型语言。静态类型语言在编译时便已确定变量的类型，而动态类型语言的变量类型要到程序运行的时候，待变量被赋予某个值之后，才会具有某种类型。

静态类型语言的优点首先是在编译时就能发现类型不匹配的错误，编辑器可以帮助提前避免程序在运行期间有可能发生的一些错误。其次，如果在程序中明确地规定了数据类型，编译器还可以针对这些信息对程序进行一些优化工作，提高程序执行速度。
静态类型语言的缺点首先是迫使程序员依照强契约来编写程序，为每个变量规定数据类型，归根结底只是辅助编写可靠性高程序的一种手段，而不是编写程序的目的。其次，类型的声明也会增加更多的代码，在程序编写过程中，这些细节会让程序员的精力从思考业务逻辑上分散开来。

动态类型语言的优点是编写的代码数量更少，看起来也更加简洁，程序员可以把精力更多地放在业务逻辑上面。虽然不区分类型在某些情况下会让程序变得难以理解，但整体而言，代码量越少，越专注于逻辑表达，对阅读程序是越有帮助的。
动态类型语言的缺点是无法保证变量的类型，从而在程序的运行期有可能发生跟类型相关的错误。

动态类型语言对变量类型的宽容给实际编码带来了很大的灵活性。由于无需进行类型检测，可以尝试调用任何对象的任意方法，而无需去考虑它原本是否被设计为拥有该方法。这一切都建立在鸭子类型（ducktyping）的概念上，鸭子类型的通俗说法是：“如果它走起路来像鸭子，叫起来也是鸭子，那么它就是鸭子。”鸭子类型指导只关注对象的行为，而不关注对象本身，也就是关注HAS-A,而不是IS-A。

在动态类型语言的面向对象设计中，鸭子类型的概念至关重要。利用鸭子类型的思想，不必借助超类型的帮助，就能轻松地在动态类型语言中实现一个原则：“面向接口编程，而不是面向实现编程”。例如，一个对象若有push和pop方法，并且这些方法提供了正确的实现，它就可以被当作栈来使用。一个对象如果有length属性，也可以依照下标来存取属性（最好还要拥有slice和splice等方法），这个对象就可以被当作数组来使用。

在静态类型语言中，要实现“面向接口编程”并不是一件容易的事情，往往要通过抽象类或者接口等将对象进行向上转型。当对象的真正类型被隐藏在它的超类型身后，这些对象才能在类型检查系统的“监视”之下互相被替换使用。只有当对象能够被互相替换使用，才能体现出对象多态性的价值。

“面向接口编程”是设计模式中最重要的思想。

#### 多态
“多态”一词源于希腊文polymorphism，拆开来看是poly（复数）+morph（形态）+ism，从字面上可以理解为复数形态。多态的实际含义是：同一操作作用于不同的对象上面，可以产生不同的解释和不同的执行结果。换句话说，给不同的对象发送同一个消息的时候，这些对象会根据这个消息分别给出不同的反馈。

多态背后的思想是将“做什么”和“谁去做以及怎样去做”分离开来，也就是将“不变的事物”与“可能改变的事物”分离开来。动物都会叫，这是不变的，但是不同类型的动物具体怎么叫是可变的。把不变的部分隔离出来，把可变的部分封装起来，这给予了扩展程序的能力，程序看起来是可生长的，也是符合开放—封闭原则的，相对于修改代码来说，仅仅增加代码就能完成同样的功能，这显然优雅和安全得多。

多态的思想实际上是把“做什么”和“谁去做”分离开来，要实现这一点，归根结底先要消除类型之间的耦合关系。如果类型之间的耦合关系没有被消除，那么在方法中指定了被执行对象是某个类型，它就不可能再被替换为另外一个类型。在Java中，可以通过向上转型来实现多态。

而JavaScript的变量类型在运行期是可变的，这意味着JavaScript对象的多态性是与生俱来的。动态类型语言，它在编译时没有类型检查的过程，既没有检查创建的对象类型，又没有检查传递的参数类型。不存在任何程度上的“类型耦合”。这正是从上一节的鸭子类型中领悟的道理。在JavaScript中，并不需要诸如向上转型之类的技术来取得多态的效果。

在《重构：改善既有代码的设计》里写到：多态的最根本好处在于，你不必再向对象询问“你是什么类型”而后根据得到的答案调用对象的某个行为——你只管调用该行为就是了，其他的一切多态机制都会为你安排妥当。换句话说，多态最根本的作用就是通过把过程化的条件分支语句转化为对象的多态性，从而消除这些条件分支语句。

利用对象的多态性，每个对象应该做什么，已经成为了该对象的一个方法，被安装在对象的内部，每个对象负责它们自己的行为。所以这些对象可以根据同一个消息，有条不紊地分别进行各自的工作。将行为分布在各个对象中，并让这些对象各自负责自己的行为，这正是面向对象设计的优点。

拿命令模式来说，请求被封装在一些命令对象中，这使得命令的调用者和命令的接收者可以完全解耦开来，当调用命令的execute方法时，不同的命令会做不同的事情，从而会产生不同的执行结果。而做这些事情的过程是早已被封装在命令对象内部的，作为调用命令的客户，根本不必去关心命令执行的具体过程。

在组合模式中，多态性使得客户可以完全忽略组合对象和叶节点对象之前的区别，这正是组合模式最大的作用所在。对组合对象和叶节点对象发出同一个消息的时候，它们会各自做自己应该做的事情，组合对象把消息继续转发给下面的叶节点对象，叶节点对象则会对这些消息作出真实的反馈。

在策略模式中，Context并没有执行算法的能力，而是把这个职责委托给了某个策略对象。每个策略对象负责的算法已被各自封装在对象内部。当对这些策略对象发出“计算”的消息时，它们会返回各自不同的计算结果。

在JavaScript这种将函数作为一等对象的语言中，函数本身也是对象，函数用来封装行为并且能够被四处传递。当对一些函数发出“调用”的消息时，这些函数会返回不同的执行结果，这是“多态性”的一种体现，也是很多设计模式在JavaScript中可以用高阶函数来代替实现的原因。

#### 封装
封装的目的是将信息隐藏。一般而言，讨论的封装是封装数据和封装实现。更广义的封装，不仅包括封装数据和封装实现，还包括封装类型和封装变化。

在许多语言的对象系统中，封装数据是由语法解析来实现的，这些语言也许提供了private、public、protected等关键字来提供不同的访问权限。
但JavaScript并没有提供对这些关键字的支持，只能依赖变量的作用域来实现封装特性，而且只能模拟出public和private这两种封装性。
除了ECMAScript6中提供的let之外，一般通过函数来创建作用域：
```js
var myObject = (function(){ var __name = 'sven'; // 私 有（ private） 变 量
  return { getName: function(){ // 公 开（ public） 方 法
    return __name;
    }
  }
})();
console.log( myObject.getName() ); // 输 出： sven
console.log( myObject.__name ) // 输 出： undefined
```
在ECAMScript6中，还可以通过Symbol创建私有属性。

封装的目的是将信息隐藏，封装应该被视为“任何形式的封装”，也就是说，封装不仅仅是隐藏数据，还包括隐藏实现细节、设计细节以及隐藏对象的类型等。

从封装实现细节来讲，封装使得对象内部的变化对其他对象而言是透明的，也就是不可见的。对象对它自己的行为负责。其他对象或者用户都不关心它的内部实现。封装使得对象之间的耦合变松散，对象之间只通过暴露的API接口来通信。当修改一个对象时，可以随意地修改它的内部实现，只要对外的接口没有变化，就不会影响到程序的其他功能。

封装实现细节的例子非常之多。拿迭代器来说明，迭代器的作用是在不暴露一个聚合对象的内部表示的前提下，提供一种方式来顺序访问这个聚合对象。编写了一个each函数，它的作用就是遍历一个聚合对象，使用这个each函数的人不用关心它的内部是怎样实现的，只要它提供的功能正确便可以。即使each函数修改了内部源代码，只要对外的接口或者调用方式没有变化，用户就不用关心它内部实现的改变。

封装类型是静态类型语言中一种重要的封装方式。一般而言，封装类型是通过抽象类和接口来进行的。把对象的真正类型隐藏在抽象类或者接口之后，相比对象的类型，客户更关心对象的行为。在许多静态语言的设计模式中，想方设法地去隐藏对象的类型，也是促使这些模式诞生的原因之一。比如工厂方法模式、组合模式等。

在JavaScript中，并没有对抽象类和接口的支持。JavaScript本身也是一门类型模糊的语言。在封装类型方面，JavaScript没有能力，也没有必要做得更多。对于JavaScript的设计模式实现来说，不区分类型是一种失色，也可以说是一种解脱。

从设计模式的角度出发，封装在更重要的层面体现为**封装变化**。
《设计模式》提到的“找到变化并封装之”。《设计模式》一书中共归纳总结了23种设计模式。从意图上区分，这23种设计模式分别被划分为创建型模式、结构型模式和行为型模式。

拿创建型模式来说，要创建一个对象，是一种抽象行为，而具体创建什么对象则是可以变化的，创建型模式的目的就是封装创建对象的变化。而结构型模式封装的是对象之间的组合关系。行为型模式封装的是对象的行为变化。
通过封装变化的方式，把系统中稳定不变的部分和容易变化的部分隔离开来，在系统的演变过程中，只需要替换那些容易变化的部分，如果这些部分是已经封装好的，替换起来也相对容易。这可以最大程度地保证程序的稳定性和可扩展性。

#### 原型模式和基于原型继承的JavaScript对象系统
在以类为中心的面向对象编程语言中，类和对象的关系可以想象成铸模和铸件的关系，对象总是从类中创建而来。而在原型编程的思想中，类并不是必需的，对象未必需要从类中创建而来，一个对象是通过克隆另外一个对象所得到的。

原型模式不单是一种设计模式，也被称为一种编程泛型。

从设计模式的角度讲，原型模式是用于创建对象的一种模式，如果想要创建一个对象，一种方法是先指定它的类型，然后通过类来创建这个对象。原型模式选择了另外一种方式，不再关心对象的具体类型，而是找到一个对象，然后通过克隆来创建一个一模一样的对象。既然原型模式是通过克隆来创建对象的，那么很自然地会想到，如果需要一个跟某个对象一模一样的对象，就可以使用原型模式。

原型模式的实现关键，是语言本身是否提供了clone方法。ECMAScript5提供了Object.create方法，可以用来克隆对象。
```js
var Plane = function(){
  this.blood = 100;
  this.attackLevel = 1;
  this.defenseLevel = 1;
};
var plane = new Plane();
    plane.blood = 500;
    plane.attackLevel = 10;
    plane.defenseLevel = 7;

var clonePlane = Object.create( plane );
console.log( clonePlane ); // 输 出： Object {blood: 500, attackLevel: 10, defenseLevel: 7}

// 在不支持Object.create方法的浏览器中，则可以使用以下代码：
Object.create = Object.create | | function( obj ){
  var F = function(){};
  F.prototype = obj;
  return new F();
}
```
克隆是创建对象的手段，原型模式的真正目的并非在于需要得到一个一模一样的对象，而是提供了一种便捷的方式去创建某个类型的对象，克隆只是创建这个对象的过程和手段。

在用Java等静态类型语言编写程序的时候，类型之间的解耦非常重要。依赖倒置原则提醒创建对象的时候要避免依赖具体类型，而用newXXX创建对象的方式显得很僵硬。工厂方法模式和抽象工厂模式可以帮助解决这个问题，但这两个模式会带来许多跟产品类平行的工厂类层次，也会增加很多额外的代码。

原型模式提供了另外一种创建对象的方式，通过克隆对象，就不用再关心对象的具体类型名字。在JavaScript这种类型模糊的语言中，创建对象非常容易，也不存在类型耦合的问题。从设计模式的角度来讲，原型模式的意义并不算大。但JavaScript本身是一门基于原型的面向对象语言，它的对象系统就是使用原型模式来搭建的，在这里称之为原型编程范型也许更合适。

原型编程范型至少包括以下基本规则:
- 所有的数据都是对象。
- 要得到一个对象，不是通过实例化类，而是找到一个对象作为原型并克隆它。
- 对象会记住它的原型。
- 如果对象无法响应某个请求，它会把这个请求委托给它自己的原型。

JavaScript在设计的时候，模仿Java引入了两套类型机制：基本类型和对象类型。基本类型包括undefined、number、boolean、string、function、object。按照JavaScript设计者的本意，除了undefined之外，一切都应是对象。为了实现这一目标，number、boolean、string这几种基本类型数据也可以通过“包装类”的方式变成对象类型数据来处理。

JavaScript中的根对象是Object.prototype对象。Object.prototype对象是一个空的对象。在JavaScript遇到的每个对象，实际上都是从Object.prototype对象克隆而来的，Object.prototype对象就是它们的原型。

在JavaScript语言里，并不需要关心克隆的细节，因为这是引擎内部负责实现的。所需要做的只是显式地调用varobj1=newObject()或者varobj2={}。此时，引擎内部会从Object.prototype上面克隆一个对象出来，最终得到的就是这个对象。
JavaScript的函数既可以作为普通函数被调用，也可以作为构造器被调用。当使用new运算符来调用函数时，此时的函数就是一个构造器。用new运算符来创建对象的过程，实际上也只是先克隆Object.prototype对象，再进行一些其他额外操作的过程。具体细节可以参阅《JavaScript语言精髓与编程实践》。

在Chrome和Firefox等向外暴露了对象`__proto__`属性的浏览器下，可以通过下面这段代码来理解new运算的过程：
```js
function Person( name ){
  this.name = name;
};
Person.prototype.getName = function(){
  return this.name;
};
var objectFactory = function(){
  var obj = new Object(), // 从 Object.prototype 上 克 隆 一 个 空 的 对 象
  Constructor = [].shift.call( arguments ); // 取 得 外 部 传 入 的 构 造 器， 此 例 是 Person
  obj.__proto__ = Constructor.prototype; // 指 向 正 确 的 原 型
  var ret = Constructor.apply( obj, arguments ); // 借 用 外 部 传 入 的 构 造 器 给 obj 设 置 属 性
  return typeof ret === 'object' ? ret : obj; // 确 保 构 造 器 总 是 会 返 回 一 个 对 象
};
var a = objectFactory( Person, 'sven' );
console.log( a.name ); // 输 出： sven
console.log( a.getName() ); // 输 出： sven
```
如果请求可以在一个链条中依次往后传递，那么每个节点都必须知道它的下一个节点。同理，要完成JavaScript语言中的原型链查找机制，每个对象至少应该先记住它自己的原型。“对象的原型”，就JavaScript的真正实现来说，其实并不能说对象有原型，而只能说对象的构造器有原型。对于“对象把请求委托给它自己的原型”这句话，更好的说法是对象把请求委托给它的构造器的原型。那么对象如何把请求顺利地转交给它的构造器的原型呢？
JavaScript给对象提供了一个名为`__proto__`的隐藏属性，某个对象的`__proto__`属性默认会指向它的构造器的原型对象，即{Constructor}.prototype。

实际上，`__proto__`就是对象跟“对象构造器的原型”联系起来的纽带。正因为对象要通过`__proto__`属性来记住它的构造器的原型，所以用objectFactory函数来模拟用new创建对象时，需要手动给obj对象设置正确的`__proto__`指向。
`obj.__proto__=Constructor.prototype;`让`obj.__proto__`指向Person.prototype，而不是原来的Object.prototype。

在JavaScript中，每个对象都是从Object.prototype对象克隆而来的，如果是这样的话，只能得到单一的继承关系，即每个对象都继承自Object.prototype对象，这样的对象系统显然是非常受限的。
实际上，虽然JavaScript的对象最初都是由Object.prototype对象克隆而来的，但对象构造器的原型并不仅限于Object.prototype上，而是可以动态指向其他对象。这样一来，当对象a需要借用对象b的能力时，可以有选择性地把对象a的构造器的原型指向对象b，从而达到继承的效果。下面的代码是最常用的原型继承方式：
```js
var obj = { name: 'sven' };
var A = function(){};
A.prototype = obj;
var a = new A();
console.log( a.name ); // 输 出： sven
```
来看看执行这段代码的时候，引擎做了哪些事情。
1. 首先，尝试遍历对象a中的所有属性，但没有找到name这个属性。
2. 查找name属性的这个请求被委托给对象a的构造器的原型，它被`a.__proto__`记录着并且指向A.prototype，而A.prototype被设置为对象obj。
3. 在对象obj中找到了name属性，并返回它的值。

当期望得到一个“类”继承自另外一个“类”的效果时，往往会用下面的代码来模拟实现：
```js
var A = function(){};
A.prototype = { name: 'sven' };

var B = function(){};
B.prototype = new A();

var b = new B();
console.log( b.name ); // 输 出： sven
```
再看这段代码执行的时候，引擎做了什么事情。
首先，尝试遍历对象b中的所有属性，但没有找到name这个属性。查找name属性的请求被委托给对象b的构造器的原型，它被`b.__proto__`记录着并且指向B.prototype，而B.prototype被设置为一个通过newA()创建出来的对象。在该对象中依然没有找到name属性，于是请求被继续委托给这个对象构造器的原型A.prototype。在A.prototype中找到了name属性，并返回它的值。

和把B.prototype直接指向一个字面量对象相比，通过B.prototype=newA()形成的原型链比之前多了一层。但二者之间没有本质上的区别，都是将对象构造器的原型指向另外一个对象，继承总是发生在对象和对象之间。

Object.create就是原型模式的天然实现。使用Object.create来完成原型继承看起来更能体现原型模式的精髓。目前大多数主流浏览器都提供了Object.create方法。但美中不足是在当前的JavaScript引擎下，通过Object.create来创建对象的效率并不高，通常比通过构造函数创建对象要慢。此外还有一些值得注意的地方，比如通过设置构造器的prototype来实现原型继承的时候，除了根对象Object.prototype本身之外，任何对象都会有一个原型。而通过Object.create(null)可以创建出没有原型的对象。

另外，ECMAScript6带来了新的Class语法。这让JavaScript看起来像是一门基于类的语言，但其背后仍是通过原型机制来创建对象。通过Class创建对象的一段简单示例代码如下所示：
```js
class Animal{
  constructor(name){
    this.name = name;
  }
  getName(){
    return this.name;
  }
}

class Dog extends Animal{
  constructor(name){
    super(name);
  }
  speak(){
    return 'woo';
  }
}

var dog = new Dog('foo');
console.log(dog.getName() + ' ' + dog.speak());
```

### this、call和apply
在JavaScript编程中，this关键字总是让人感到迷惑，Function.prototype.call和Function.prototype.apply这两个方法也有着广泛的运用。

#### this
跟别的语言大相径庭的是，JavaScript的this总是指向一个对象，而具体指向哪个对象是在运行时基于函数的执行环境动态绑定的，而非函数被声明时的环境。

##### this的指向
除去不常用的with和eval的情况，具体到实际应用中，this的指向大致可以分为以下4种。
- 作为对象的方法调用。
- 作为普通函数调用。
- 构造器调用。
- Function.prototype.call或Function.prototype.apply调用。

**1. 作为对象的方法调用**
当函数作为对象的方法被调用时，this指向该对象：
```js
var obj = {
  a: 1,
  getA: function(){ alert ( this === obj ); // 输 出： true
  alert ( this.a ); // 输 出: 1
  }
};
obj.getA();
```

**2. 作为普通函数调用**
当函数不作为对象的属性被调用时，也就是常说的普通函数方式，此时的this总是指向全局对象。在浏览器的JavaScript里，这个全局对象是window对象。
```js
window.name = 'globalName';
var getName = function() {
    return this.name;
  };
console.log(getName()); // 输 出： globalName

// 或
window.name = 'globalName';
var myObject = {
  name: 'sven',
  getName: function() {
    return this.name;
  }
};
var getName = myObject.getName;
console.log(getName()); // globalName
```
有时候会遇到一些困扰，比如在div节点的事件函数内部，有一个局部的callback方法，callback被作为普通函数调用时，callback内部的this指向了window，但往往是想让它指向该div节点，见如下代码：
```js
window.id = 'window';
document.getElementById('div1').onclick = function() {
  alert(this.id); // 输 出：' div1'
  var callback = function() {
      alert(this.id); // 输 出：' window'
    }
  callback();
};
```
此时有一种简单的解决方案，可以用一个变量保存div节点的引用：
```js
window.id = 'window';
document.getElementById('div1').onclick = function() {
  var that = this; // 保存div引用
  var callback = function() {
      alert(that.id); // 输 出：' window'
    }
  callback();
};
```
在ECMAScript5的strict模式下，这种情况下的this已经被规定为不会指向全局对象，而是undefined：
```js
function func(){
  "use strict"
  alert ( this ); // 输 出： undefined
}
func();
```

**3. 构造器调用**
JavaScript中没有类，但是可以从构造器中创建对象，同时也提供了new运算符，使得构造器看起来更像一个类。

除了宿主提供的一些内置函数，大部分JavaScript函数都可以当作构造器使用。构造器的外表跟普通函数一模一样，它们的区别在于被调用的方式。当用new运算符调用函数时，该函数总会返回一个对象，通常情况下，构造器里的this就指向返回的这个对象，见如下代码：
```js
var MyClass = function(){ this.name = 'sven'; };

var obj = new MyClass();
alert ( obj.name ); // 输 出： sven
```
但用new调用构造器时，还要注意一个问题，如果构造器显式地返回了一个object类型的对象，那么此次运算结果最终会返回这个对象，而不是之前期待的this：
```js
var MyClass = function() {
    this.name = 'sven';
    return { // 显 式 地 返 回 一 个 对 象
      name: 'anne'
    }
  };
var obj = new MyClass();
alert(obj.name); // 输 出： anne
```
如果构造器不显式地返回任何数据，或者是返回一个非对象类型的数据，就不会造成上述问题：
```js
var MyClass = function() {
    this.name = 'sven'
    return 'anne'; // 返 回 string 类 型
  };
var obj = new MyClass();
alert(obj.name); // 输 出： sven
```

**4. Function.prototype.call或Function.prototype.apply调用**
跟普通的函数调用相比，用Function.prototype.call或Function.prototype.apply可以动态地改变传入函数的this：
```js
var obj1 = {
  name: 'sven',
  getName: function() {
    return this.name;
  }
};
var obj2 = {
  name: 'anne'
};
console.log(obj1.getName()); // 输 出: sven
console.log(obj1.getName.call(obj2)); // 输 出： anne
```
call和apply方法能很好地体现JavaScript的函数式语言特性，在JavaScript中，几乎每一次编写函数式语言风格的代码，都离不开call和apply。在JavaScript诸多版本的设计模式中，也用到了call和apply

##### 丢失的this
这是一个经常遇到的问题，先看下面的代码：
```js
var obj = {
  myName: 'sven',
  getName: function() {
    return this.myName;
  }
};
console.log(obj.getName()); // 输 出：' sven'
var getName2 = obj.getName;
console.log(getName2()); // 输 出： undefined
```
当调用obj.getName时，getName方法是作为obj对象的属性被调用的，此时的this指向obj对象，所以obj.getName()输出'sven'。

当用另外一个变量getName2来引用obj.getName，并且调用getName2时，此时是普通函数调用方式，this是指向全局window的，所以程序的执行结果是undefined。

再看另一个例子，document.getElementById这个方法名实在有点过长，大概尝试过用一个短的函数来代替它，如同prototype.js等一些框架所做过的事情：
```js
var getId = function( id ){ return document.getElementById( id ); };
getId( 'div1' );
```
也许思考过为什么不能用下面这种更简单的方式：
```js
var getId = document.getElementById;
getId( 'div1' );
```
在Chrome、Firefox、IE10中执行过后就会发现，这段代码抛出了一个异常。这是因为许多引擎的document.getElementById方法的内部实现中需要用到this。这个this本来被期望指向document，当getElementById方法作为document对象的属性被调用时，方法内部的this确实是指向document的。

但当用getId来引用document.getElementById之后，再调用getId，此时就成了普通函数调用，函数内部的this指向了window，而不是原来的document。可以尝试利用apply把document当作this传入getId函数，帮助“修正”this：
```js
document.getElementById = (function(func) {
  return function() {
    return func.apply(document, arguments);
  }
})(document.getElementById);
var getId = document.getElementById;
var div = getId('div1');
alert(div.id); // 输 出： div1
```

#### call和apply
ECAMScript3给Function的原型定义了两个方法，它们是Function.prototype.call和Function.prototype.apply。在实际开发中，特别是在一些函数式风格的代码编写中，call和apply方法尤为有用。在JavaScript版本的设计模式中，这两个方法的应用也非常广泛，能熟练运用这两个方法，是真正成为一名JavaScript程序员的重要一步。

##### call和apply的区别
Function.prototype.call和Function.prototype.apply都是非常常用的方法。它们的作用一模一样，区别仅在于传入参数形式的不同。

apply接受两个参数，第一个参数指定了函数体内this对象的指向，第二个参数为一个带下标的集合，这个集合可以为数组，也可以为类数组，apply方法把这个集合中的元素作为参数传递给被调用的函数：
```js
var func = function(a, b, c) {
    alert([a, b, c]); // 输 出 [ 1, 2, 3 ]
  };
func.apply(null, [1, 2, 3]);
```
在这段代码中，参数1、2、3被放在数组中一起传入func函数，它们分别对应func参数列表中的a、b、c。call传入的参数数量不固定，跟apply相同的是，第一个参数也是代表函数体内的this指向，从第二个参数开始往后，每个参数被依次传入函数：
```js
var func = function(a, b, c) {
    alert([a, b, c]); // 输 出 [ 1, 2, 3 ]
  };
func.call(null, 1, 2, 3);
```
当调用一个函数时，JavaScript的解释器并不会计较形参和实参在数量、类型以及顺序上的区别，JavaScript的参数在内部就是用一个数组来表示的。从这个意义上说，apply比call的使用率更高，不必关心具体有多少参数被传入函数，只要用apply一股脑地推过去就可以了。

call是包装在apply上面的一颗语法糖，如果明确地知道函数接受多少个参数，而且想一目了然地表达形参和实参的对应关系，那么也可以用call来传送参数。

当使用call或者apply的时候，如果传入的第一个参数为null，函数体内的this会指向默认的宿主对象，在浏览器中则是window：
```js
var func = function(a, b, c) {
    alert(this === window); // 输 出 true
  };
func.apply(null, [1, 2, 3]);
```
但如果是在严格模式下，函数体内的this还是为null：

有时候使用call或者apply的目的不在于指定this指向，而是另有用途，比如借用其他对象的方法。那么可以传入null来代替某个具体的对象：
```js
Math.max.apply( null, [ 1, 2, 5, 3, 4 ] ) // 输 出： 5
```

##### call和apply的用途
在实际开发中的用途

**1.改变this指向**
call和apply最常见的用途是改变函数内部的this指向，来看个例子：
```js
var obj1 = {
  name: 'sven'
};
var obj2 = {
  name: 'anne'
};
window.name = 'window';
var getName = function() {
    alert(this.name);
  };
getName(); // 输 出: window
getName.call(obj1); // 输 出: sven
getName.call(obj2); // 输 出: anne
```
当执行getName.call(obj1)这句代码时，getName函数体内的this就指向obj1对象，所以此处的
```js
var getName = function(){ alert ( this.name ); };
```
实际上相当于：
```js
var getName = function(){ alert ( obj1. name );};
```
在实际开发中，经常会遇到this指向被不经意改变的场景，比如有一个div节点，div节点的onclick事件中的this本来是指向这个div的：
```js
document.getElementById('div1').onclick = function() {
  alert(this.id); // 输 出： div1
};
```
假如该事件函数中有一个内部函数func，在事件内部调用func函数时，func函数体内的this就指向了window，而不是预期的div，见如下代码：
```js
document.getElementById('div1').onclick = function() {
  alert(this.id); // 输 出： div1
  var func = function() {
      alert(this.id); // 输 出： undefined
    }
  func();
};
```
这时候用call来修正func函数内的this，使其依然指向div：
```js
document.getElementById('div1').onclick = function() {
  var func = function() {
      alert(this.id); // 输 出： div1
    }
  func.call(this);
};
```
使用call来修正this的场景，比如修正document.getElementById函数内部“丢失”的this，代码如下：
```js
document.getElementById = (function(func) {
  return function() {
    return func.apply(document, arguments);
  }
})(document.getElementById);
var getId = document.getElementById;
var div = getId('div1');
alert(div.id); // 输 出： div1
```

**2. Function.prototype.bind**
大部分高级浏览器都实现了内置的Function.prototype.bind，用来指定函数内部的this指向，即使没有原生的Function.prototype.bind实现，来模拟一个也不是难事，代码如下：
```js
Function.prototype.bind = function(context) {
  var self = this; // 保 存 原 函 数
  return function() { // 返 回 一 个 新 的 函 数
    return self.apply(context, arguments); // 执 行 新 的 函 数 的 时 候， 会 把 之 前 传 入 的 context 当 作 新 函 数 体 内 的 this
  }
};
var obj = {
  name: 'sven'
};
var func = function() {
    alert(this.name); // 输 出： sven
  }.bind(obj);
func();
```
通过Function.prototype.bind来“包装”func函数，并且传入一个对象context当作参数，这个context对象就是想修正的this对象。

在Function.prototype.bind的内部实现中，先把func函数的引用保存起来，然后返回一个新的函数。当在将来执行func函数时，实际上先执行的是这个刚刚返回的新函数。在新函数内部，self.apply(context,arguments)这句代码才是执行原来的func函数，并且指定context对象为func函数体内的this。

这是一个简化版的Function.prototype.bind实现，通常还会把它实现得稍微复杂一点，使得可以往func函数中预先填入一些参数：
```js
Function.prototype.bind = function() {
  var self = this,
    // 保 存 原 函 数
    context = [].shift.call(arguments),
    // 需 要 绑 定 的 this 上 下 文
    args = [].slice.call(arguments); // 剩 余 的 参 数 转 成 数 组
  return function() { // 返 回 一 个 新 的 函 数
	// 执 行 新 的 函 数 的 时 候， 会 把 之 前 传 入 的 context 当 作 新 函 数 体 内 的 this
	// 并 且 组 合 两 次 分 别 传 入 的 参 数， 作 为 新 函 数 的 参 数
    return self.apply(context, [].concat.call(args, [].slice.call(arguments)));
  }
};
var obj = {
  name: 'sven'
};
var func = function(a, b, c, d) {
    alert(this.name); // 输 出： sven
    alert([a, b, c, d]) // 输 出：[ 1, 2, 3, 4 ]
  }.bind(obj, 1, 2);
func(3, 4);
```

**3. 借用其他对象的方法**
借用方法的第一种场景是“借用构造函数”，可以实现一些类似继承的效果：
```js
var A = function(name) {
    this.name = name;
  };
var B = function() {
    A.apply(this, arguments);
  };
B.prototype.getName = function() {
  return this.name;
};
var b = new B('sven');
console.log(b.getName()); // 输 出： 'sven'
```
第二种运用场景，函数的参数列表arguments是一个类数组对象，虽然它也有“下标”，但它并非真正的数组，所以也不能像数组一样，进行排序操作或者往集合里添加一个新的元素。这种情况下，常常会借用Array.prototype对象上的方法。

比如想往arguments中添加一个新的元素，通常会借用Array.prototype.push：
```js
(function() {
  Array.prototype.push.call(arguments, 3);
  console.log(arguments); // 输 出[ 1,2,3]
})(1, 2);
```
在操作arguments的时候，经常非常频繁地找Array.prototype对象借用方法。

想把arguments转成真正的数组的时候，可以借用Array.prototype.slice方法；想截去arguments列表中的头一个元素时，又可以借用Array.prototype.shift方法。

这种机制的内部实现原理是什么呢？V8的引擎源码，以Array.prototype.push为例：
```js
function ArrayPush() {
  var n = TO_UINT32(this.length); // 被 push 的 对 象 的 length
  var m = % _ArgumentsLength(); // push 的 参 数 个 数
  for (var i = 0; i < m; i++) {
    this[i + n] = % _Arguments(i); // 复 制 元 素 (1)
  }
  this.length = n + m; // 修 正 length 属 性 的 值 (2)
  return this.length;
};
```
通过这段代码可以看到，Array.prototype.push实际上是一个属性复制的过程，把参数按照下标依次添加到被push的对象上面，顺便修改了这个对象的length属性。至于被修改的对象是谁，到底是数组还是类数组对象，这一点并不重要。

由此可以推断，可以把“任意”对象传入Array.prototype.push：
```js
var a = {};
Array.prototype.push.call(a, 'first');
alert(a.length); // 输 出： 1
alert(a[0]); // first
```
这段代码在绝大部分浏览器里都能顺利执行，但由于引擎的内部实现存在差异，如果在低版本的IE浏览器中执行，必须显式地给对象a设置length属性：
```js
var a = { length: 0 };
```
前面之所以把“任意”两字加了双引号，是因为可以借用Array.prototype.push方法的对象还要满足以下两个条件，从ArrayPush函数的(1)处和(2)处也可以猜到，这个对象至少还要满足：
- 对象本身要可以存取属性；
- 对象的length属性可读写。

对于第一个条件，对象本身存取属性并没有问题，但如果借用Array.prototype.push方法的不是一个object类型的数据，而是一个number类型的数据呢?无法在number身上存取其他数据，那么从下面的测试代码可以发现，一个number类型的数据不可能借用到Array.prototype.push方法：
```js
var a = 1;
Array.prototype.push.call(a, 'first');
alert(a.length); // 输 出： undefined
alert(a[0]); // 输 出： undefined
```
对于第二个条件，函数的length属性就是一个只读的属性，表示形参的个数，尝试把一个函数当作this传入Array.prototype.push：
```js
var func = function() {};
Array.prototype.push.call(func, 'first');
alert(func.length); // 报 错： cannot assign to read only property ‘length’ of function(){}
```