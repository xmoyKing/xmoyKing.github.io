---
title: JavaScript高级程序设计-11-面向对象2-创建对象
categories: JavaScript
tags:
  - JavaScript
  - js-pro
date: 2016-08-10 18:32:09
updated:
---

接上篇[JavaScript高级程序设计-10-面向对象1-理解对象属性](/2016/08/10/js-pro10)

### 创建对象
虽然Object构造函数或对象字面量都可以用来创建单个对象，但有明显的缺点：使用同一个接口创建多个对象会产生大量的重复代码，工厂模式的一个变体就可以解决这个问题。

#### 工厂模式
工厂模式是一种常见的软件设计模式，这种模式抽象了创建具体对象的过程。考虑ES无法创建类，因此使用函数代替，通过函数以特定接口来封装创建对象的细节，如下例：
```js
function createPerson(name, age, job){
  var o = new Object();
  o.name = name;
  o.age = age;
  o.job = job;
  o.sayName = function(){
    console.log(this.name);
  }
  return o;
}

var p1 = createPerson('king', 21, 'software engineer');
var p2 = createPerson('tom', 22, 'software manager');
```
通过函数createPerson能够根据接受的参数来构建一个包含所有必要信息的Person对象，可以无数次调用这个函数，且每次返回一个包含特定属性和方法的对象。工厂模式虽然解决了创建多个相似对象的问题，但却没有解决对象识别问题（即如何确定对象的类型，而用typeof仅仅只能知道都是object类型）。构造函数模式则解决了对象识别问题。

#### 构造函数模式
ES中的构造函数可用来创建特定类型的对象，像Object和Array这样的原生构造函数，在运行时会自动出现在执行环境中，此外，也可以创建自定义的构造函数，从而定义自定义对象类型的属性和方法，例如，使用构造函数模式重写例子：
```js
function Person(name, age, job){
  this.name = name;
  this.age = age;
  this.job = job;
  this.sayName = function(){
    console.log(this.name);
  }
}

var p1 = new Person('king', 21, 'software engineer');
var p1 = new Person('tom', 22, 'software manager');
```
上例中，Person函数取代createPerson函数，同时，函数实现中有如下不同：
- 没有显式的创建object对象
- 直接将属性和方法赋值给了this对象
- 没有return语句
此外，按照惯例，构造函数名的命名方式为大写首字母（大驼峰），借鉴其他OO语言，为了区别ES中的其他函数，因为构造函数本身也是一个函数，只不过这个函数专门用来创建对象。

要创建Person的实例，必须使用new操作符，实际背后的执行流程如下：
1. 创建一个新对象
2. 将构造函数的作用域赋给新对象（因此this就指向了这个新对象）
3. 执行构造函数中的代码（为这个新对象添加属性）
4. 返回新对象

本例中，p1和p2分别保存这个一个Person的实例，这两个对象都有一个constructor（构造函数）属性，该属性指向Person：
```js
p1.constructor == Person; // true
p2.constructor == Person; // true
```
对象的constructor属性最初是用来表示对象类型的，但检测对象类型，还是用instanceof操作符更可靠一些：
```js
p1 instanceof Person; // true
p2 instanceof Person; // true

p1 instanceof Object; // true
p2 instanceof Object; // true
```
创建自定义的构造函数意味着可以用它的实例标识为一种特定的类型，而这正是构造函数模式胜过工厂模式的地方。

同时需要注意的是以本例这种方式定义的构造函数是定义在Global对象中的（浏览器下为window对象）

##### 将构造函数当做函数
构造函数与其他函数唯一区别就在于调用方式不同。但构造函数毕竟时函数，不存在定义构造函数的特殊语法。任何函数只要通过new操作符调用，那么它就作为构造函数。而任何函数不通过new操作符调用则与普通函数无区别,上例的Person构造函数不通过new操作符调用也可以：
```js
// 当做普通函数使用
Person('king', 21, 'software engineer'); // 对象会被添加到window中
window.sayName(); // 'king'

// 在另一个作用域中调用
var o = new Object();
Person.call(o, 'king', 21, 'software engineer');
o.sayName(); // 'king'
```
本例中，当不使用new操作符调用Person时，属性和方法都被添加到window对象中，因为当在全局作用域中调用一个函数时，this对象总是指向Global对象，因此在调用完函数后，可以通过window对象来调用sayName方法。

最后，通过call在某特殊对象的作用域中调用Person函数，本例是在o对象的作用域中调用的，因此o就拥有了属性和方法。

##### 构造函数的问题
构造函数模式虽然好用，但也有缺点。主要问题是每个方法都需要在每个实例上重新创建一遍，在前面的例子中，p1和p2都有一个名为sayName的方法，但两个方法却不是同一个Function的实例（在ES中函数也是对象，因此每个函数被定义也就是实例化了一个Function对象），所以从逻辑上此时的构造函数应该这样定义：
```js
function Person(name, age, job){
  this.name = name;
  this.age = age;
  this.job = job;
  this.sayName = new Function("console.log(this.name);"); // 与声明函数在逻辑上等价
}
```
从本例更容易理解每个Person实例都包含一个不同的Function实例的本质，即，以这种方式创建函数，会导致不同作用域和标识符解析，但创建Function新实例的机制仍然是相同的，因此，不同实例上的同名函数是不相等的：
```js
p1.sayName == p2.sayName; // false
```
理论上，确实没有必要创建多个同样功能的Function实例，况且有this对象在，根本不用在执行代码前就把函数绑定到特定对象上，因此，可考虑将函数定义转移到构造函数外部：
```js
function Person(name, age, job){
  this.name = name;
  this.age = age;
  this.job = job;
  this.sayName = sayName;
}
function sayName(){
  console.log(this.name);
}
```
这样，相当于在构造函数内部将sayName属性设置为全局的sayName函数。由于sayNaem包含的时一个指向函数的指针，因此p1和p2对象就共享了在全局作用域中定义的同一个sayName函数，这就解决了两个函数功能相同的问题。

但新问题又有了，在全局作用域定义的函数实际上却只能被某个对象调用，这样全局作用域有点不对。同时，若对象需要定义很多方法那么就必须同时定义多个全局函数了，这样会极大破坏封装。

以上的问题通过原型模式可解决。

#### 原型模式
每一次创建的函数都有一个prototype（原型）属性，这个属性是一个指针，指向一个对象，而这个对象的用途是：包含可以由可定类型的所有实例共享的属性和方法。即不必在构造函数中定义对象实例的信息，而将这些信息直接添加到原型对象中：
```js
function Person(){};

Person.prototype.name = "king";
Person.prototype.age = 22;
Person.prototype.job = "software engineer";
Person.prototype.sayName = function(){
  console.log(this.name);
};

var p1 = new Person();
p1.sayName(); // 'king'
var p2 = new Person();

p1.sayName == p2.sayName; // true
```
上例将方法和属性都直接添加到Person的原型中，构造函数变为空函数。由于原型中所有的属性和方法由所有实例共享，因此p1和p2访问的都是同一组属性和方法。

要理解原型模式的工作原理，需要理解ES中原型对象的性质：

##### 理解原型对象
无论何时，只要创建了一个新函数，就会根据一组特定的规则为该函数创建一个prototype属性，这个属性指向函数的原型对象，在默认情况下，所有原型对象都会自动获得一个constructor（构造函数）属性，这个属性包含一个指向prototype属性所在函数的指针。就前例来说：Person.prototype.constructor指向Person，而通过这个构造函数，还可以继续为原型对象添加其他属性和方法。

创建了自定义的构造函数后，其原型对象默认只会取得constructor属性，至于其他方法，则都是从Object继承而来，当调用构造函数创建一个新实例后，该实例的内部将包含一个指针（内部属性），指向构造函数的原型对象。在ES5中，这个指针被称为`[[prototype]]`，但由于以前没有规定标准的访问`[[prototype]]`的方法，所以浏览器在所有实例上实现了一个`__proto__`属性用于访问`[[prototype]]`。

需要明确：真正重要的时这个连接存在与实例与构造函数的原型对象之间，而不是存在与实例与构造函数之间。
![prototype](1.png)
上图展示了Person构造函数、Person的原型属性、以及Person的实例之间的关系：
Person.prototype指向了原型对象，而Person.prototype.constructor又指回了Person。原型对象中除了包含constructor属性之外，还包含后来添加的其他属性。
Person的每个实例p1和p2都包含一个内部属性，该属性仅仅指向Person。prototype，即，它们与构造函数没有直接的关系，此外，需要注意，虽然这两个实例都不包含属性和方法，但却可以通过实例来调用sayName方法，实现原理与查找对象属性的过程相同。

虽然没有标准的访问`[[prototype]]`的方法，但可通过isPrototypeOf方法来确定对象之间是否存在这种关系，从本质讲，若`[[prototype]]`指向调用isPrototypeOf方法的对象（Person.prototype），那么返回true：
```js
Person.prototype.isPrototypeOf(p1); // true
```

其实，ES5添加了一个新方法，叫Object.getPrototypeOf，这个方法会返回`[[prototype]]`的值:
```js
Object.getPrototypeOf(p1) == Person.prototype; // true
Object.getPrototypeOf(p1).name; // 'king'
```
每当代码读取某个对象的某个属性时，都会执行一次搜索，目标时具有给定名字的属性，搜索先从对象实例本身开始，然后依次搜索指针指向的原型对象。

当在实例中添加属性时，会在实例中创建该属性并屏蔽原型对象中的同名属性，但不会修改原型中的同名属性，通过delete操作符删除实例属性，则能够重新访问原型的同名属性。

通过hasOwnProperty方法能检测一个属性是否存在实例中，因为这个方法只在给定属性存在于对象实例中时才会返回true。

ES5中的Object.getOwnPropertyDescriptor方法只能用于实例属性，若要取得原型属性的描述符，则必须在原型对象上调用该方法。

##### 原型与in操作符
有两种使用in操作符的方法：单独使用或在for-in循环中使用。

单独使用时，in操作符只会在通过对象能够访问给定属性时返回true，无论该属性存在实例还是原型中：
```js
var p1 = new Person();
"name" in p1; // true
```
与hasOwnProperty方法搭配则可以确定属性到底时存在实例中还是存在原型中。
```js
function hasPrototypeProperty(obj, name){
  return !obj.hasOwnProperty(name) && (name in obj);
}
```

在使用for-in循环时，返回的时所有能够通过对象访问的、可枚举的（enumberable）属性，即包含实例中的，也包含原型中的。默认所有自定义的属性都是可枚举的。

ES5的Object.keys方法接收一个对象作为参数，返回一个包含所有可枚举的实例属性的字符串数组。

若想要得到所有的实例属性，无论是否可枚举，可以使用Object.getOwnPropertyNames方法。

##### 更简单的原型语法
当需要定义多个方法和属性时，通常是用一个包含所有属性和方法的对象字面量来重写整个原型对象，这样能减少Person.prototype的使用：
```js
function Person(){};

Person.prototype = {
  name: "king",
  age: 22,
  job: "software engineer",

  sayName: function(){
    console.log(this.name);
  }
}
```
但这样做会导致constructor属性不再指向Person，而是指向了Object构造函数，因为用一个新的对象字面量覆盖了默认的对象原型（但instanceof操作符依然能正确使用），所以若constructor比较重要时，需要显式的将其重设：
```js
var p1 = new Person();
p1 instanceof Object; // true
p1 instanceof Person; // true
p1.constructor == Object; // true
p1.constructor == Person; // false

Person.prototype = {
  constructor: Person,
  name: "king",
  age: 22,
  job: "software engineer",

  sayName: function(){
    console.log(this.name);
  }
}

var p2 = new Person();
p2.constructor == Person; // true
```
而显示设置constructor会导致其`[[Enumerable]]`特性被设置为true，默认情况下，原生的contructor属性时不可枚举的，因此最好通过Object.defineProperty方法显式设置constructor属性：
```js
Object.defineProperty(Person.prototype, 'constructor',{
  enumerable: false,
  value: Person
});
```

##### 原型的动态性
由于原型查找的过程其实是不断回溯搜索的过程，因此对原型对象的任何修改都将立即从实例上反映出来，即使先创建了实例后修改原型也是如此。
```js

function Person(){}

Person.prototype = {
  constructor: Person,
  name: "king",
  age: 22,
  job: "software engineer",

  sayName: function(){
    console.log(this.name);
  }
}
var p1 = new Person();

p1.sayName(); // 'king'

Person.prototype.sayHi = function(){
  console.log('Hi');
}

p1.sayHi(); // 'Hi'
```
原因可归结为实例与原型之间的松散链接关系，实例与原型之间的连接是一个指针而不是一个副本，因此可以通过原型获取到最新的方法或属性。

但需要注意的是，若重写了整个原型对象，则会导致访问错误并报错，因为调用构造函数时会为实例添加一个指向最初原型的`[[Prototype]]`指针，而将原型修改为另一个对象就等于切断了构造函数与最初原型之间的联系（**实例中的指针仅指向原型，而不是指向构造函数**）：
```js
function Person(){}

var p1 = new Person();

Person.prototype = {
  constructor: Person,
  name: "king",
  age: 22,
  job: "software engineer",

  sayName: function(){
    console.log(this.name);
  }
}

p1.sayName(); // Uncaught SyntaxError: Invalid shorthand property initializer
```

##### 原生对象的原型
原型模式的重要性不仅体现在创建自定义类型方面，就连所有原生的引用类型，都是采用这种模式创建的。所有原生引用类型（Object、Array、String等）在都其构造函数的原型上定义了方法，例如，通过Array.prototype可以找到sort方法。
```js
typeof Array.prototype.sort; // 'function'
```
通过原生对象的原型、不仅可以取得所有默认方法的引用，而且可以定义新方法，就像修改自定义对象的原型一样修改原生对象的原型，因此可以随时添加或修改方法。

但不推荐在产品化程序中修改原生类型对象的原型，因为不仅会非常依赖实现（添加的特定方法可能在某些作用域或实现中没有）并且可能意外修改原生方法。

##### 原型对象的问题
原型模式也不是没有缺点，首先省略了为构造函数传递初始化参数的环节，导致所有实例在默认情况下都将获得相同的属性值。

其次，由于其共享的本质，原型中所有属性被所有实例贡献，这种共享对函数是非常适合的，对包含基本值的属性也没问题（可覆盖原型中的同名属性），但对引用类型值则有时会出大问题（修改一个会影响到所有实例）：
```js
function Person(){}

Person.prototype = {
  constructor: Person,
  name: "king",
  age: 22,
  job: "software engineer",
  links: ['tom', 'jim', 'shally'],

  sayName: function(){
    console.log(this.name);
  }
}

var p1 = new Person();
var p2 = new Person();

p1.links.push('mix');

p1.links; // ['tom', 'jim', 'shally', 'mix']
p2.links; // ['tom', 'jim', 'shally', 'mix']
p1.links == p2.links; // true
```
上例中，理论上每个person实例应该拥有属于自己的人际关系，但由于引用类型的共享问题，导致所有实例都共享一个links数组。

所以若不确定是否需要对所有实例都开放共享同一个引用类型时，不要使用这样的原型模式。

#### 组合使用构造函数模式和原型模式
创建自定义类型的最常见方式就是组合使用构造函数模式和原型模式，集两种模式的优点于一身,也是目前定义引用类型的一种默认模式。

构造函数模式用于定义实例属性，而原型模式用于定义方法和共享的属性，这样一来，每个实例会有自己的一份实例属性的副本、但同时也能贡献对方法的引用，最大限度节约内存。同时，这样的混合模式还支持向构造函数传递参数。

重写前面的例子：
```js
function Person(name, age, job){
  this.name = name;
  this.age = age;
  this.job = job;
  this.links = ['tom', 'jim', 'shally'];
}

Person.prototype = {
  constructor: Person,

  sayName: function(){
    console.log(this.name);
  }
}


var p1 = new Person('king', 21, 'software engineer');
var p2 = new Person('bom', 22, 'software manager');
```

#### 动态原型模式
动态原型模式用于解决构造函数和原型彼此独立的问题，其他OO语言不是彼此独立的，而是将所有信息封装在构造函数中，且通过构造函数中初始化原型，保持了同时使用构造函数和原型优点，即，通过检查某个应存在的方式是否有效来决定是否需要初始化原型：
```js
function Person(name, age, job){
  // 属性
  this.name = name;
  this.age = age;
  this.job = job;

  // 方法
  if(typeof this.sayName != 'function'){
    Person.prototype.sayName = function(){
      console.log(this.name);
    };
  }
}

var p1 = new Person('king', 21, 'software engineer');
p1.sayName();
```
注意上述的if判断语句，只有在sayName不存在时，才会将它添加到原型中。这段代码只会在初次调用构造函数时执行，然后，原型已经初始完成，不需要在做什么修改了。

但由于原型修改会立即反映到所有实例上，所以这样的方式可以说非常完美。

尤其是不需要if检测每个属性和方法，只用检测一个就好了，也可以通过instanceof操作符来确定它的类型。

#### 寄生构造函数模式
若前面几种模式都不适用时，可使用寄生（parasitic）构造函数模式，这种模式的基本思想是：创建一个函数，该函数的作用仅仅时封装创建对象的代码，然后在返回新创建的对象，从表面上看，与典型的工厂模式非常相似。
```js
function Person(name, age, job){
  var o = new Object();
  o.name = name;
  o.age = age;
  o.job = job;
  o.sayName = function(){
    console.log(this.name);
  }
  return o;
}

var p1 = new Person('king', 21, 'software engineer');
p1.sayName(); // 'king'
```
本例中，除了使用new操作符把所使用的包装函数当做构造函数使用之外，这个模式和工厂模式一模一样。Person函数创建了一个新对象，并以相应的属性和方法初始化该对象，最后返回这个对象。

其实，构造函数（必须是使用new操作符的才能称为构造函数）在不返回值的情况下，默认会返回新对象的实例，而通过在构造函数的末尾添加一个return语句，可以重写调用构造函数时返回的值。

这种模式特点就是，在特殊情况下为对象创建构造函数。假设需要创建一个具有额外方法的特殊数组，但由于不能直接修改Array构造函数，此时就非常适合了：
```js
function SpecialArray(){
  var a = new Array(); // 创建数组实例
  a.push.apply(a, arguments);   // 添加值
  a.toPipedSting = function(){ // 添加方法
    return this.join('|');
  }
  return a;
}
var color = new SpeicalArray('red','blue','green');

color.toPipedSting(); // 'red|blue|green'
```
关于这个模式，需要注意，首先，返回的对象与构造函数或与构造函数的原型属性之间没有关系，即，构造函数返回的对象与构造函数外部创建的对象没有什么不同，因此，不能依赖instanceof操作符来确定对象类型，所以，不到万不得已尽量不要使用这种模式。

#### 稳妥构造函数模式
稳妥对象（durable objects）是由Douglas Crockford提出的概念，指的时没有公共的属性，且其方法不引用this对象。这种对象最适合在对安全有特定要求的环境下（即禁用this和new）使用，或防止数据被其他应用修改时使用。

稳妥构造函数模式与寄生构造函数模式类似，但不同的是，即不使用this也不使用new：
```js
function Person(name, age, job){
  var o = new Object();
  o.sayName = function(){
    console.log(name);
  }
  return o;
}
var p1 = Person('king', 21, 'software engineer');
p1.sayName(); // 'king'
```
这种情况下，创建的对象除了使用sayName之外，没有任何方法可以访问name值，即使其他代码能给这个对象添加方法或属性，也不能访问传入到构造函数中的原始数据。这种安全性使得其非常适合用于某些安全执行环境。