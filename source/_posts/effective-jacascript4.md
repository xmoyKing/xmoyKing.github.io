---
title: effective-jacascript笔记-4
date: 2017-03-01 11:30:28
tags:
  - js
  - effective javascript
  - note
---

## 对象和原型

### 30. 理解prototype, getPrototypeOf 和 __proto__之间的不同
原型包含三个独立但相关的访问器. 这三个访问器的命名对`prototype`属性做了一些变化.
1. `C.prototype`用于建立由`new C()`创建的对象的原型
2. `Object.getPrototypeOf(obj)`是ES5中用来获取`obj`对象的原型对象的标准方法
3. `obj.__proto__`是获取`obj`对象的原型对象的非标准方法.

```js
function User(name, pwHash){
    this.name = name;
    this.pwHash = pwHash;
}

User.prototype.toString = function(){
    return '[User ' + this.name + ']';
}

User.prototype.checkPw = function(pw){
    return hash(pw) === this.pwHash;
}

var u = new User('ssss', '0ef678sdsg567afh8dadsadasd');
```

`User`函数带有一个默认的`prototype`属性,其包含一个开始几乎为空的对象. 在上例中,添加了两个方法到`User.prototype`对象. `toString`和`checkPw`方法. 当使用`new`操作符创建`User`的实例时, 产生的对象`u`会自动获得分配的原型对象, 这个对象存储在`User.prototype`中.

![User构造函数及其实例的原型关系](1.png)

构造函数的`prototype`属性用来设置新实例的原型关系.


ES5中的函数`Object.getPrototypeOf()`可以用于检索现有对象的原型. 如:   
```js
Object.gePrototypeOf(u) === User.prototype; // true
```


非标准的检索对象原型的方法, `__proto__`属性.
```js
u.__proto__ === User.prototype; // true
```

JS中的类 本质上是一个构造函数(`User`)与一个用于在该类(`User.prototype`)实例间共享方法的原型对象的结合.

1. **C.prototype属性是new C()创建的对象的原型**
2. **Object.getPrototypeOf(obj)是ES5中检索对象原型的标准函数**
3. **obj.__proto__是检索对象原型的非标准方法**
4. **类是由一个构造函数和一个关联的原型组成的一种设计模式**

### 31. 使用Object.getPrototypeOf函数而不是使用__proto__属性
无论何时,`getPrototypeOf`函数都是有效的,而且它是提取对象原型更加标准,可移植的方法. 由于`__proto__`属性会污染所有的对象,因此会有一些它引发的错误.

### 32. 始终不要修改__proto__属性
__proto__属性提供了修改对象原型链的能力,而`Object.getPrototypeOf()`方法却不能修改. 所以尽量不要修改此属性,会破坏程序的可移植问题. 

另一个问题是性能, 所有的现代JS引擎都深度优化了获取和设置对象属性的行为, 因为这些都是一些最常见的JS程序的操作. 这些优化都是基于引擎在对象结构的认识上, 当修改对象的内部结构(如添加或删除该对象或其原型链中的对象的属性), 将会使一些优化失效. 

修改`__proto__`属性实际上改变了继承结构本身. 比起普通属性,修改`__proto__`会导致更多的优化失效.

避免修改`__proto__`属性最大的原因是为了保障程序行为的可预测性, 对象的原型链通过一套确定的属性及属性值来定义它的行为.

修改对象的原型链会交换对象的整个继承层次结构, 某些情况下这些操作可能会有用,但保持继承层次结构稳定是基本准则.

### 33. 使构造函数与new操作符无关
当使用类型30条中的User函数创建一个构造函数时, 程序需要依赖`new`操作符来调用该构造函数, 若忘记使用`new`关键字,则函数的接收者将会是全局对象.
```js
var u = User('bbbb', '790af7657ds6ad45adsa');
u; // undefined
this.name; // 'bbbb'
this.pwHash; // '790af7657ds6ad45adsa'
```
该函数不仅会返回无意义的`undefined`,而且会创建/修改全局变量`name`和`pwHash`. 

若在严格模式下,那么它的接收者默认为`undefined`. 这种情况下,错误的调用会导致错误, `User`的第一行试图给`this.name`赋值时, 会抛出`TypeError`错误.
```js
function User(name, pwHash){
    'use strict';
    this.name = name;
    this.pwHash = pwHash;
}
var u = User('bbbb', '790af7657ds6ad45adsa');  // error: this is undefined
```

可以用一个简单的方法检测函数的接收者是否为正确的`User`实例,即检测是否使用`new`操作符
```js
function User(name, pwHash){
    if(!(this instanceof User)){
        return new User(name, pwHash);
    }
    this.name = name;
    this.pwHash = pwHash;
}
```

使用这种方式,不管是以普通函数还是以构造函数的方式调用`User`函数,它都返回一个继承自`User.prototype`的对象.
```js
var x = User('bbbb', '790af7657ds6ad45adsa');
var y = new User('bbbb', '790af7657ds6ad45adsa');
x instanceof User; // true
y instanceof User; // true
```
但这种方式的缺点是需要额外的函数调用, 而且很难适用与可变参数函数, 因为没有一种直接模拟`apply`方法将可变参数函数作为构造函数调用的方式.

还有一个使用`Object.create()`函数的方法解决`new`操作符的问题.
```js
function User(name, pwHash){
    var self = this instanceof User ? this : Object.create(User.prototype);
                            
    self.name = name;
    self.pwHash = pwHash;
    return self;
}
```
`Object.create()`需要一个原型对象作为模版, 并返回一个继承自该原型对象的新对象. 因此,当以函数的方式调用该`User`函数时, 结果将返回一个继承自`User.prototype`的新对象,并且该对象具有已经初始化的`name`和`pwHash`属性.

`Object.create()`是ES5引进的, 在一些老的或不支持此特性的浏览器中,可通过创建一个局部的构造函数并使用`new`操作符初始化该构造函数来替代`Object.create()`.
```js
if(typeof Object.create === 'undefined'){
    Object.create = function(prototype){
        function C(){}
        C.prototype = prototype;
        return new C();
    }
}
```
上述版本仅是单参数版本的, 完整版本的`Object.create()`函数还接受一个可选参数, 用于描述一组定义在新对象上的属性描述符.

若使用`new`操作符调用该新版本的`User`函数会发生什么? 由于构造函数覆盖模式, 使用`new`操作符调用的行为就如函数调用它的行为一样. 

构造函数覆盖模式即JS允许`new`表达式的结果可以被构造函数中的显示`return`语句所覆盖. 当`User`函数返回`self`对象时, `new` 表达式的结果就变成self对象, 该对象可能是另一个绑定到`this`的对象.

防范误用构造函数可能没有那么重要!尤其是在局部作用域内使用构造函数的时候.

但最有用的在于理解若以错误的方式调用构造函数会照成的严重后果, 且在文档花构造函数期望使用`new`操作符调用是很重要, 尤其是在跨大型代码库中共享构造函数或该构造函数来自一个共享库时.

1. **通过使用new操作符或Object.create方法在构造函数定义中调用自身使得该构造函数与调用语法无关.**
2. **当一个函数期望使用new操作符调用时,清晰地文档化该函数**

### 34. 在原型中存储方法
JS完全有可能不借助原型链编程, 比如30条中的`User`类, 不在原型中定义方法.
```js
function User(name, pwHash){    
    this.name = name;
    this.pwHash = pwHash;

    this.toString = function(){
        return '[User ' + this.name + ']';
    };
    this.checkPw = function(pw){
        return hash(pw) === this.pwHash;
    };
}
```
大多数情况下都能正常运行,但若构造多个`User`类的实例时, 区别就暴露了.

```js
var u1 = new User(/**/);
var u2 = new User(/**/);
var u3 = new User(/**/);
…
```

![将方法存储在实例对象中](2.png)

上图为三个对象及他们的原型对象结构图, 每个实例都包含`toString`和`checkPw`方法的副本, 而不是通过原型共享这些的方法,所以会有6个"相同的"函数对象.

![将方法存储在原型中](3.png)

相反,若用原型链的方式, `toString`和`checkPw`方法只被创建一次,对象实例间通过原型共享. 将方法存储在原型中,使其可以被所有的实例使用, 而不需要存储方法实现的多个副本, 也不需要给每个实例对象增加额外的属性.

在查找方法的速度上, 现代JS引擎深度优化了原型查找, 所以将方法复制到实例对象并不一定保证查找速度明显提升, 且实例方法比起原型方法会占用更多内存.

### 35. 使用闭包存储私有数据
JS的对象系统并没有鼓励信息隐藏, 所有的属性名都是一个字符串, 任意一段程序都可以简单地通过访问属性名来获取相应的对象属性. 例如 `for…in`循环,ES5中的`Object.keys()`和`Object.hasOwnPropertyNames()`函数都能轻易获取对象的所有属性名.

通常使用编码规范来"创建"私有属性, 如在命名的时候加上下划线`_`,这是一种命名规范, 表明对对象的正确行为操作的一种建议.

JS提供了一种信息隐藏的机制——**闭包**.

闭包将数据存储到封闭的变量中而不提供对这些变量的直接访问, 获取闭包内部结构的唯一方式是该函数显示地提供获取它的方法, 也就是说, 与普通对象相反, 对象的属性会被自动的暴露出去,而闭包则自动隐藏起来.

利用这种特性在对象中存储真正的私有数据, 不是将数据作为对象的属性存储,而是在构造函数中以变量的方式来存储, 并将对象的方法转变为引用这些变量的闭包.
```js
function User(name, pwHash){    
    this.toString = function(){
        return '[User ' + name + ']';
    };
    this.checkPw = function(pw){
        return hash(pw) === pwHash;
    };
}
```
**注意**: 此处的`toString`和`checkPw`方法是以变量的方式来引用`name`和`pwHash`变量的, 而不是以`this`属性的方式来引用. 现在, `User`的实例不包含任何实例属性, 因此外部的代码不能直接访问`User`实例的`name`和`pwHash`变量.

该方式的缺点是, 为了让构造函数中的变量存在于使用它们的方法作用域内, 这些方法必须置于实例对象中. 这会导致副本的扩散.

1. **闭包变量是私有的, 只能通过局部的引用获取**
2. **将局部变量作为私有数据从而通过方法实现信息隐藏**

### 36. 只将实例状态存储在实例对象中
理解原型对象与其实例之间的一对多的关系对于实现正确的对象行为是非常重要的. 错误的做法是将每一实例的数据存储到原型中.

如: 一个树型数据结构, 将存储子节点的数组放置在原型对象中将会导致实现被完全破坏
```js
function Tree(x){
    this.value = x;
}
Tree.prototype = {
    children: [], // 此属性应该作为实例状态
    addChild: function(x){
        this.children.push(x);
    }
};

// 当使用此类构造一课树
var left = new Tree(2);
left.addChild(1);
left.addChild(3);

var right = new Tree(6);
right.addChild(5);
right.addChild(7);

var top = new Tree(4);
top.addChild(left);
top.addChild(right);

top.children; // [1, 3, 5, 7, left, right];
```
每次调用`addChild`方法, 都会将值添加到`Tree.prototype.children`数组中. `Tree.prototype.children`数组包含了任何地方按序调用`addChild`方法时传入的所有节点.
![将实例状态存储在原型对象中](4.png)

实现`Tree`类的正确方法是为每个实例对象创建一个单独的`children`数组.
```js
function Tree(x){
    this.value = x;
    this.children = []; // 实例状态
}
Tree.prototype = {
    addChild: function(x){
        this.children.push(x);
    }
};
```
![将实例状态存储在实例对象中](5.png)

通常在一个类的多个实例之间共享方法是安全的, 因为方法通常是无状态的, 这不同于通过`this`来引用实例状态,(因为方法调用的语法确保了`this`被绑定到实例对象, 即使该方法是从原型中继承来的,共享方法仍然可以访问实例状态)

一般情况下,任何不可变的数据可以被存储在原型中从而被安全的共享, 有状态的数据原则上也可以存储在原型中, 但原型对象中一般是存储方法, 而每个实例状态存储在实例对象中.

1. **共享可变数据可能会出问题, 因为原型是被其所有的实例共享的**
2. **将可变的实例状态存储在实例对象中**


### 37. 认识到this变量的隐式绑定问题
编写一个读取CSV(逗号分割型取值)数据的类, 构造函数需要一个可选的分隔器字符数组并构造出一个自定义的正则表达式将每一行分为不同的条目.
```js
function CVSReader(separators){
    this.separators = separators || [","];
    this.regexp = new RegExp(this.separators.map(function(sep){
        return "\\" + sep[0];
    }).join("|"));
}

